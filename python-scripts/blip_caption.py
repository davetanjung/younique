from flask import Flask, request, jsonify, g # Make sure g is imported
from PIL import Image
from flask_cors import CORS
from transformers import BlipProcessor, BlipForConditionalGeneration
import torch
import logging
import os
from dotenv import load_dotenv

load_dotenv()
# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

# Use GPU if available
device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
logger.info(f"Using device: {device}")

# Load model and processor once (Moved before routes for clarity and to ensure it's loaded)
try:
    logger.info("Loading BLIP model...")
    processor = BlipProcessor.from_pretrained("Salesforce/blip-image-captioning-base")
    model = BlipForConditionalGeneration.from_pretrained("Salesforce/blip-image-captioning-base").to(device)
    logger.info("BLIP model loaded successfully")
except Exception as e:
    logger.error(f"Error loading model: {str(e)}")
    raise # Or handle more gracefully if you prefer the app to start with a broken model

@app.before_request
def check_api_key():
    # --- Bypass API key check for the /health endpoint ---
    if request.path == '/health':
        # g.skip_auth = True # Using g here isn't strictly necessary if you just return
        logger.info("Skipping API key check for /health endpoint.")
        return # Don't do anything else, let the /health route handle it

    # --- Regular API key check for other endpoints ---
    # g.skip_auth = False # Also not strictly needed here
    expected_key = os.getenv("PYTHON_SERVER_KEY")
    logger.info(f"--- check_api_key for {request.path} ---")
    logger.info(f"Expected PYTHON_SERVER_KEY from env: '{expected_key}'")
    request_key = request.headers.get("X-API-Key")
    logger.info(f"Received X-API-Key header: '{request_key}'")

    if expected_key:
        if request_key != expected_key:
            logger.warning(f"API Key Mismatch. Expected: '{expected_key}', Got: '{request_key}'. Returning 401.")
            return jsonify({"error": "Unauthorized"}), 401
        else:
            logger.info("API key validated successfully.")
    else:
        logger.warning("PYTHON_SERVER_KEY is NOT SET in Python's environment. API key check skipped (SECURITY RISK if intended).")

@app.route("/health", methods=["GET"])
def health_check():
    # This check will now be hit directly if request.path was '/health' in before_request
    logger.info("--- /health endpoint hit ---")
    return jsonify({"status": "healthy", "model": "blip-image-captioning"}), 200

@app.route("/caption", methods=["POST"])
def caption_image():
    # This will only be reached if check_api_key didn't return a 401 for /caption
    logger.info("--- /caption endpoint hit ---") # Changed from "Received caption request" for consistency
    # The "Verify API key if enabled" comment is now handled by before_request

    if "image" not in request.files:
        logger.warning("No image in request")
        return jsonify({"error": "No image uploaded"}), 400

    try:
        file = request.files["image"]
        if not file.filename.lower().endswith(('.png', '.jpg', '.jpeg', '.gif')):
            logger.warning(f"Invalid file format: {file.filename}")
            return jsonify({"error": "Invalid file type"}), 400

        logger.info(f"Processing image: {file.filename}")
        raw_image = Image.open(file.stream).convert("RGB")

        prompt = request.form.get("prompt", "Describe this clothing item in detail including color and type.")
        logger.info(f"Using prompt: {prompt}")

        inputs = processor(raw_image, prompt, return_tensors="pt").to(device)

        with torch.no_grad():
            out = model.generate(**inputs, max_length=50, num_return_sequences=3, num_beams=5)

        caption = [processor.decode(o, skip_special_tokens=True) for o in out]
        logger.info(f"Generated caption: {caption}")

        return jsonify({"caption": caption})

    except Exception as e:
        logger.error(f"Error processing image: {str(e)}")
        return jsonify({"error": str(e)}), 500

# REMOVED DUPLICATE /health route that was here

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5001)) # Ensure this is the correct port
    debug = os.environ.get("FLASK_DEBUG", "false").lower() == "true"
    
    logger.info(f"Attempting to start caption service on port {port} (debug={debug})")
    try:
        app.run(host="0.0.0.0", debug=debug, port=port)
        # logger.info(f"Successfully started caption service on port {port}") # This line won't be reached as app.run() blocks
    except OSError as e:
        logger.error(f"OSError when starting app on port {port}: {e}")
        if "Address already in use" in str(e):
            logger.error(f"Port {port} is already in use. Please check other applications or change the port.")
        else:
            logger.error(f"Could not start the server on port {port}. Error: {e}")
    except Exception as e:
        logger.error(f"An unexpected error occurred while trying to start the server: {e}")