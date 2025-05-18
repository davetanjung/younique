<?php

namespace App\Http\Controllers;

use App\Models\Cloth;
use Auth;
use DB;
use Illuminate\Http\Request;
use Storage;
use Str;
use GuzzleHttp\Client;
use Log;

class ClothController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $guestId = session()->get('guest_id');

        if (!$userId && !$guestId) {
            $guestId = Str::uuid()->toString();
            session()->put('guest_id', $guestId);
        }

        $clothes = Cloth::when($userId, function ($query) use ($userId) {
            return $query->where('user_id', $userId);
        })
            ->when(!$userId, function ($query) use ($guestId) {
                return $query->where('guest_id', $guestId);
            })
            ->get();

        return view('e-wardrobe.myclothes', compact('clothes', 'guestId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Store image locally first
            $path = $request->file('image')->store('clothes/images', 'public');
            $imageUrl = Storage::url($path);

            // Get user identifiers
            $userId = Auth::id();
            $guestId = $userId ? null : session()->get('guest_id', Str::uuid()->toString());
            
            if (!$userId) {
                session()->put('guest_id', $guestId);
            }

            // Define a fallback caption
            $fallbackCaption = "Clothing item";
            $caption = $fallbackCaption;
            
            // Try to get caption from Python server
            try {
                $pythonServerUrl = env('PYTHON_SERVER_URL', 'http://localhost:5001/caption');
                
                Log::info('Attempting to connect to Python server at: ' . $pythonServerUrl);
                
                $client = new Client([
                    'timeout' => 30,
                    'headers' => [
                        'Accept' => 'application/json',
                    ]
                ]);

                // Build the multipart form data
                $multipartData = [
                    [
                        'name' => 'image',
                        'contents' => fopen($request->file('image')->getPathname(), 'r'),
                        'filename' => $request->file('image')->getClientOriginalName(),
                    ],
                    [
                        'name' => 'prompt',
                        'contents' => ''
                    ]
                ];

                // Set up request options with headers separately
                $options = [
                    'multipart' => $multipartData,
                    'headers' => [
                        'Accept' => 'application/json',
                    ]
                ];

                // Add API key if configured - FIX: Properly add the API key to the headers
                if (env('PYTHON_SERVER_KEY')) {
                    $serverKey = env('PYTHON_SERVER_KEY');
                    $options['headers']['X-API-Key'] = $serverKey;
                    Log::info('Using API key for Python server request: Key is set');
                } else {
                    Log::warning('No PYTHON_SERVER_KEY found in environment');
                }

                $response = $client->post($pythonServerUrl, $options);
                $result = json_decode($response->getBody()->getContents(), true);
                
                if (!empty($result['caption']) && is_array($result['caption'])) {
                    // Take the first caption from the array
                    $caption = $result['caption'][0];
                    Log::info('Successfully received caption: ' . $caption);
                } else if (!empty($result['caption']) && is_string($result['caption'])) {
                    $caption = $result['caption'];
                    Log::info('Successfully received caption: ' . $caption);
                } else {
                    Log::warning('Received empty or invalid caption from Python server');
                }
            } catch (\Exception $e) {
                Log::error('Error calling Python caption service: ' . $e->getMessage());
                // Continue with fallback caption
            }

            $parsedAttributes = $this->parseCaption($caption);
            
            $cloth = Cloth::create([
                'image_url' => $imageUrl,
                'caption' => $caption,
                'user_id' => $userId,
                'guest_id' => $guestId,
                'name' => $parsedAttributes['name'],
                'color' => $parsedAttributes['color'],
                'type' => $parsedAttributes['type'],
                'category' => $parsedAttributes['category'],
                'season' => $parsedAttributes['season'],
            ]);

            DB::commit();
            
            return redirect()->route('cloth.index')->with('success', 'Clothing item added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete the stored image if it exists
            if (isset($path)) {
                Storage::delete('public/' . $path);
            }
            
            Log::error('Failed to add clothing item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Parse the AI-generated caption to extract clothing attributes
     */
    private function parseCaption($caption)
    {
        // Log the raw caption for debugging
        Log::info("Parsing caption: " . $caption);
        
        $attributes = [
            'name' => 'Clothing Item',
            'color' => 'unknown',
            'type' => 'top', 
            'category' => 'casual', 
            'season' => 'all-season', 
        ];
        
        if (!$caption) {
            return $attributes;
        }
        
        // Extract name - use the full caption as a default name
        $attributes['name'] = ucfirst(trim($caption));
        
        // Extract color - expanded list and improved detection
        $colors = [
            'black', 'white', 'red', 'blue', 'green', 'yellow', 'purple', 'pink', 
            'brown', 'gray', 'grey', 'orange', 'beige', 'tan', 'navy', 'maroon', 
            'teal', 'turquoise', 'silver', 'gold', 'cream', 'ivory', 'khaki', 'olive'
        ];
        
        // Check for specific color patterns first
        if (preg_match('/\b(dark|light|pale|bright|deep)\s+(\w+)\b/i', $caption, $matches)) {
            $colorModifier = strtolower($matches[1]);
            $baseColor = strtolower($matches[2]);
            
            if (in_array($baseColor, $colors)) {
                $attributes['color'] = $colorModifier . ' ' . $baseColor;
                Log::info("Detected modified color: {$attributes['color']}");
            }
        }
        
        // If no modified color was found, look for simple colors
        if ($attributes['color'] === 'unknown') {
            foreach ($colors as $color) {
                if (preg_match('/\b' . $color . '\b/i', $caption)) {
                    $attributes['color'] = $color;
                    Log::info("Detected color: {$color}");
                    break;
                }
            }
        }
        
        // Improved type detection with more clothing items
        $topKeywords = [
            'shirt', 't-shirt', 'tee', 'blouse', 'top', 'sweater', 'hoodie', 
            'sweatshirt', 'jacket', 'coat', 'blazer', 'dress', 'cardigan'
        ];
        
        $bottomKeywords = [
            'pants', 'jeans', 'shorts', 'skirt', 'trousers', 'leggings', 
            'sweatpants', 'joggers', 'chinos', 'slacks'
        ];
        
        // Check for tops
        foreach ($topKeywords as $keyword) {
            if (preg_match('/\b' . $keyword . '\b/i', $caption)) {
                $attributes['type'] = 'top';
                Log::info("Detected top item: {$keyword}");
                break;
            }
        }
        
        // Check for bottoms (will override tops if found)
        foreach ($bottomKeywords as $keyword) {
            if (preg_match('/\b' . $keyword . '\b/i', $caption)) {
                $attributes['type'] = 'bottom';
                Log::info("Detected bottom item: {$keyword}");
                break;
            }
        }
        
        // Expanded category detection
        $categories = [
            'formal' => ['suit', 'dress', 'formal', 'tuxedo', 'gown', 'tie', 'blazer', 'evening'],
            'sportswear' => ['athletic', 'sport', 'jersey', 'workout', 'gym', 'running', 'fitness', 'training'],
            'business' => ['business', 'professional', 'office', 'work', 'corporate'],
            'loungewear' => ['pajama', 'lounge', 'sleeping', 'relaxed', 'comfortable', 'casual'],
            'party' => ['party', 'clubbing', 'festive', 'celebration', 'fancy', 'elegant']
        ];
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (preg_match('/\b' . $keyword . '\b/i', $caption)) {
                    $attributes['category'] = $category;
                    Log::info("Detected category: {$category} from keyword: {$keyword}");
                    break 2;
                }
            }
        }
        
        // Improved season detection
        $seasons = [
            'summer' => ['summer', 'hot', 'beach', 'tank', 'shorts', 'sandals', 'light', 'warm weather'],
            'winter' => ['winter', 'warm', 'thick', 'coat', 'jacket', 'heavy', 'wool', 'sweater', 'cold', 'snow'],
            'fall' => ['fall', 'autumn', 'cardigan', 'light jacket', 'layering'],
            'spring' => ['spring', 'light', 'floral', 'pastel', 'rain']
        ];
        
        foreach ($seasons as $season => $keywords) {
            foreach ($keywords as $keyword) {
                if (preg_match('/\b' . $keyword . '\b/i', $caption)) {
                    $attributes['season'] = $season;
                    Log::info("Detected season: {$season} from keyword: {$keyword}");
                    break 2;
                }
            }
        }
        
        // Log the final attributes for debugging
        Log::info("Final parsed attributes: " . json_encode($attributes));
        
        return $attributes;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // Add these methods to your ClothController

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, string $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'color' => 'required|string|max:255',
        'type' => 'required|string|in:top,bottom',
        'category' => 'required|string',
        'season' => 'required|string',
    ]);

    try {
        $cloth = Cloth::findOrFail($id);
        
        // Check ownership (either user_id or guest_id must match)
        if (Auth::check()) {
            if ($cloth->user_id !== Auth::id()) {
                abort(403);
            }
        } else {
            if ($cloth->guest_id !== session()->get('guest_id')) {
                abort(403);
            }
        }

        $updateData = [
            'name' => $request->name,
            'color' => $request->color,
            'type' => $request->type,
            'category' => $request->category,
            'season' => $request->season,
        ];

        // Handle image update if provided
        if ($request->hasFile('image')) {
            // Delete old image
            if ($cloth->image_url) {
                $oldPath = str_replace('/storage', '', $cloth->image_url);
                Storage::delete('public' . $oldPath);
            }
            
            // Store new image
            $path = $request->file('image')->store('clothes/images', 'public');
            $updateData['image_url'] = Storage::url($path);
        }

        $cloth->update($updateData);

        return redirect()->route('cloth.index')->with('success', 'Clothing item updated successfully!');

    } catch (\Exception $e) {
        Log::error('Failed to update clothing item: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}

/**
 * Remove the specified resource from storage.
 */
public function destroy(string $id)
{
    try {
        $cloth = Cloth::findOrFail($id);
        
        // Check ownership (either user_id or guest_id must match)
        if (Auth::check()) {
            if ($cloth->user_id !== Auth::id()) {
                abort(403);
            }
        } else {
            if ($cloth->guest_id !== session()->get('guest_id')) {
                abort(403);
            }
        }

        // Delete the image file
        if ($cloth->image_url) {
            $path = str_replace('/storage', '', $cloth->image_url);
            Storage::delete('public' . $path);
        }

        $cloth->delete();

        return redirect()->route('cloth.index')->with('success', 'Clothing item deleted successfully!');

    } catch (\Exception $e) {
        Log::error('Failed to delete clothing item: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
}