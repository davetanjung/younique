# ai_outfit_matcher.py
from flask import Flask, request, jsonify
import os
import logging
import json
import re
from dotenv import load_dotenv # Make sure this is installed: pip install python-dotenv
import google.generativeai as genai
from google.api_core.exceptions import GoogleAPIError

# ---vvv DEBUGGING .env LOADING vvv---
print("--- STARTUP: ai_outfit_matcher.py ---")
print("Attempting to load .env file...")
# Determine the path to the .env file relative to this script file
# This assumes .env is in the same directory as this script
script_dir = os.path.dirname(os.path.abspath(__file__))
dotenv_path = os.path.join(script_dir, '.env')
print(f"Looking for .env at: {dotenv_path}")

if os.path.exists(dotenv_path):
    print(f".env file FOUND at {dotenv_path}.")
    # load_dotenv will search for '.env' in the current working directory or parent directories by default.
    # Explicitly providing the path ensures it loads the one next to the script if you run the script from elsewhere.
    # However, if you always cd into the script's directory before running, load_dotenv() without args is fine.
    # Forcing override and verbose for debugging:
    loaded_successfully = load_dotenv(dotenv_path=dotenv_path, override=True, verbose=True)
    if loaded_successfully:
        print(".env file loaded successfully by python-dotenv.")
    else:
        print(".env file was found, but python-dotenv reported it did not load new variables (maybe empty or all already set).")
else:
    print(f".env file NOT FOUND at {dotenv_path}. Environment variables must be set manually or script run from a different CWD.")

# Check specifically for GOOGLE_API_KEY AFTER attempting to load .env
google_api_key_value = os.getenv('GOOGLE_API_KEY')
print(f"Value of GOOGLE_API_KEY from os.getenv after dotenv attempt: '{google_api_key_value[:10] if google_api_key_value else 'NOT SET or EMPTY'}{'...' if google_api_key_value and len(google_api_key_value) > 10 else ''}'")
print("--- END DEBUGGING .env LOADING ---")
# ---^^^ DEBUGGING .env LOADING ^^^---


# --- Logging Setup ---
# This needs to happen AFTER potentially loading .env if FLASK_DEBUG is in there,
# but the logger instance needs to be created before use.
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__) # Define logger for the module

# --- Google Gemini Client Initialization ---
GEMINI_MODEL_NAME = 'gemini-1.5-flash-latest'
gemini_model_instance = None
logger.info("Attempting to initialize Google GenAI client...") # Moved this log here

try:
    # GOOGLE_API_KEY is already fetched and printed above for debugging
    if not google_api_key_value: # Use the already fetched value
        logger.error("GOOGLE_API_KEY is not set or is empty after .env load attempt.")
        raise ValueError("GOOGLE_API_KEY environment variable not set.")

    logger.info("Configuring genai with API key...")
    genai.configure(api_key=google_api_key_value) # Use the fetched value
    logger.info("genai configured. Initializing GenerativeModel...")
    gemini_model_instance = genai.GenerativeModel(GEMINI_MODEL_NAME)
    logger.info(f"Google GenAI client and model '{GEMINI_MODEL_NAME}' initialized successfully.")

except ValueError as ve:
    logger.error(f"ValueError during GenAI client initialization: {ve}")
except Exception as e:
    logger.error(f"Fatal error initializing Google GenAI client: {e}")
    import traceback
    logger.error(traceback.format_exc())

app = Flask(__name__)

# ... (the rest of your Flask app code: SERVICE_API_KEY check, routes, etc.) ...
# ... (No changes needed for the rest of the file from your last version) ...

# Make sure SERVICE_API_KEY is defined if used in before_request
SERVICE_API_KEY = os.getenv("AI_OUTFIT_MATCHER_KEY")

@app.before_request
def check_service_api_key():
    # Allow unauthenticated access to a health check endpoint for this service
    if request.path == '/health_matcher': # Example path
        logger.info("Skipping API key check for /health_matcher")
        return

    if SERVICE_API_KEY:
        request_key = request.headers.get("X-API-Key")
        if request_key != SERVICE_API_KEY:
            logger.warning(f"Invalid service API key for AI Outfit Matcher. Path: {request.path}")
            return jsonify({"error": "Unauthorized service access"}), 401
    # else: # Optional: log if SERVICE_API_KEY is not set and thus security check is skipped
        # logger.info("SERVICE_API_KEY not set, skipping API key check for this service.")


def format_clothes_list_for_prompt(clothes_list):
    formatted_string = "Available clothes:\n"
    if not clothes_list:
        return formatted_string + "No clothes provided.\n"
    for item in clothes_list:
        details = []
        item_id_str = f"ID: {item.get('id', 'NOT_PROVIDED')}"
        details.append(item_id_str)
        for key, value in item.items():
            if key != 'id' and value: # Only include if value exists and not already id
                details.append(f"{key.capitalize()}: {value}")
        formatted_string += f"- {', '.join(details)}\n"
    return formatted_string

@app.route("/match-outfit", methods=["POST"])
def match_outfit_endpoint():
    if not gemini_model_instance:
        logger.error("Match outfit request received, but Google GenAI model is not initialized.")
        return jsonify({"error": "AI model not initialized. Please check server logs."}), 500

    try:
        data = request.get_json()
        if not data:
            logger.warning("No JSON data received in /match-outfit request.")
            return jsonify({"error": "No JSON data received"}), 400

        clothes_list = data.get('clothes_list', [])
        occasion = data.get('occasion', 'a general day')
        context = data.get('context', '') # e.g., date, weather, season

        if not clothes_list:
            logger.warning("No clothes list provided in /match-outfit request.")
            return jsonify({"error": "No clothes list provided. Cannot generate outfit."}), 400

        system_instruction = "You are a professional fashion stylist. Your task is to select the best outfit from the user's wardrobe based on the occasion and contextual details."
        clothes_details_for_prompt = format_clothes_list_for_prompt(clothes_list)
        user_content_prompt = f"""
        **Task Context:**
        Occasion: {occasion}
        Details: {context}

        **Available Wardrobe Items:**
        {clothes_details_for_prompt}

       ## Instructions:
- You must return a JSON object in this exact format:
  {"selected_ids": [id1, id2, id3]}
- The list must include:
  - EITHER one top AND one bottom
  - OR one dress (not combined with top/bottom)
  - Optionally, add one outerwear item if suitable (max 1)
- Do not include accessories, shoes, or duplicated item types
- Avoid repeating items worn recently (if specified)
- Choose pieces that are stylish, appropriate for the occasion, and color-coordinated


        "Generate a complete outfit consisting of a top and bottom that match in style and color coordination. Ensure the top and bottom are appropriate for the same category (e.g., casual, formal, business, or sporty) and complement each other in color. The outfit should be visually appealing and look like it was curated by a fashion stylist. Avoid clashing colors and mismatched formality levels. Provide one top and one bottom that go well together as a cohesive outfit."

        **Output Format STRICTLY REQUIRED:**
        Your response MUST be a valid JSON object containing a single key named "selected_ids".
        The value for "selected_ids" MUST be a list of integers representing the IDs of the chosen clothes.
        Example of a valid response: {{"selected_ids": [123, 456, 789]}}
        Example if no suitable outfit can be formed: {{"selected_ids": []}}
        Do not add any other text, explanation, or markdown formatting before or after the JSON object.
        """
        full_prompt_for_gemini = f"{system_instruction}\n\n{user_content_prompt}"

        logger.info(f"Sending to Gemini ({GEMINI_MODEL_NAME}): Full prompt length: {len(full_prompt_for_gemini)} chars")

        generation_config = genai.types.GenerationConfig(
            candidate_count=1,
            temperature=0.2,
            max_output_tokens=256
        )

        response = gemini_model_instance.generate_content(
            full_prompt_for_gemini,
            generation_config=generation_config
        )

        if not response.candidates or not response.candidates[0].content.parts:
            block_reason = "Unknown reason"
            if response.prompt_feedback and response.prompt_feedback.block_reason:
                block_reason = response.prompt_feedback.block_reason.name
            logger.warning(f"Gemini response was empty or blocked. Reason: {block_reason}. Full response: {response}")
            return jsonify({"error": f"AI response was empty or blocked (Reason: {block_reason}).", "selected_ids": []}), 200

        ai_response_content = response.text
        logger.info(f"Gemini Raw Response Text: {ai_response_content}")

        try:
            processed_content = ai_response_content.strip()
            if processed_content.startswith("```json"):
                processed_content = processed_content.strip("```json").strip("```").strip()
            elif processed_content.startswith("```"):
                 processed_content = processed_content.strip("```").strip()
            if not processed_content:
                raise json.JSONDecodeError("Empty content after stripping markdown", "", 0)

            parsed_response = json.loads(processed_content)
            selected_ids = parsed_response.get("selected_ids")

            if selected_ids is None:
                 logger.warning(f"Gemini response missing 'selected_ids' key. Response: {processed_content}")
                 selected_ids = []
            elif not isinstance(selected_ids, list):
                logger.warning(f"Gemini returned 'selected_ids' but it's not a list. Type: {type(selected_ids)}, Value: {selected_ids}. Defaulting to empty list.")
                selected_ids = []
            else:
                valid_ids = []
                for id_val in selected_ids:
                    try:
                        valid_ids.append(int(id_val))
                    except (ValueError, TypeError):
                        logger.warning(f"Non-integer value '{id_val}' found in selected_ids list from Gemini. Skipping it.")
                selected_ids = valid_ids

            logger.info(f"Successfully parsed Gemini response. Selected IDs: {selected_ids}")
            return jsonify({"selected_ids": selected_ids})

        except json.JSONDecodeError as e:
            logger.error(f"Error parsing Gemini JSON response: {e}. Raw content after stripping was: '{processed_content}'")
            ids_from_text = re.findall(r'\b(\d+)\b', ai_response_content)
            selected_ids_fallback = [int(id_str) for id_str in ids_from_text]
            if selected_ids_fallback:
                 logger.info(f"Fallback: Extracted IDs using regex: {selected_ids_fallback}")
                 return jsonify({"selected_ids": selected_ids_fallback, "note": "Used fallback ID extraction due to JSON parsing error."})
            return jsonify({"error": "AI response format error, could not parse selected IDs.", "raw_response": ai_response_content}), 500

    except GoogleAPIError as e:
        logger.error(f"Google API Error: {e}")
        status_code = 500
        if hasattr(e, 'grpc_status_code'):
            if e.grpc_status_code == 7: status_code = 403
            elif e.grpc_status_code == 8: status_code = 429
            elif e.grpc_status_code == 3: status_code = 400
        return jsonify({"error": f"AI service API error: {e}"}), status_code
    except Exception as e:
        logger.error(f"Unexpected error in /match-outfit: {str(e)}")
        import traceback
        logger.error(traceback.format_exc())
        return jsonify({"error": f"An internal error occurred: {str(e)}"}), 500

@app.route("/health_matcher", methods=["GET"])
def health_check_matcher():
    if gemini_model_instance:
        return jsonify({"status": "healthy", "model_provider": "Google Gemini", "model_name": GEMINI_MODEL_NAME}), 200
    else:
        return jsonify({"status": "unhealthy", "reason": "Gemini model not initialized"}), 500

if __name__ == "__main__":
    port = int(os.getenv("AI_OUTFIT_PORT", 5003))

    debug_mode_str = os.getenv("FLASK_DEBUG", "false").lower()
    is_debug_mode = debug_mode_str == "true" or debug_mode_str == "1"

    logger.info(f"Attempting to start AI Outfit Matcher service (Google Gemini) on port {port} (debug={is_debug_mode})")
    try:
        # Flask's app.run debug parameter takes a boolean
        app.run(host="0.0.0.0", port=port, debug=is_debug_mode)
    except OSError as e:
        logger.error(f"OSError when starting AI Outfit Matcher on port {port}: {e}")
    except Exception as e:
        logger.error(f"Unexpected error starting AI Outfit Matcher: {e}")