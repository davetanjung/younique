<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Planner;
use App\Models\Outfit;
use App\Models\Cloth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Str;

class PlannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentDate = date('m-d');
        $userId = Auth::check() ? Auth::id() : null;
        $guestId = session()->get('guest_id');

        if (!$userId && !$guestId) {
            $guestId = Str::uuid()->toString();
            session()->put('guest_id', $guestId);
        }
        $plannerEntries = Planner::with('outfit.clothes')
            ->where('guest_id', $guestId)
            ->get();

        return view('planner.index', compact('currentDate', 'plannerEntries', 'guestId'));
    }

    public function getMonthlyEntries(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // FIX: Use consistent guest_id method - from session storage, not session ID
        $guestId = session()->get('guest_id');

        // Check if planner entries exist for this month and guest
        $existingEntries = Planner::where('guest_id', $guestId)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        // If no entries exist, generate them
        if ($existingEntries === 0) {
            $this->generateOutfitsForMonth($guestId, $startDate, $endDate);
        }

        $plannerEntries = Planner::with(['outfit.clothes']) // Eager load relationships
            ->where('guest_id', $guestId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        return response()->json($plannerEntries);
    }

    /**
     * Handle the request to generate monthly outfits (HTTP endpoint)
     */
    public function generateMonthlyOutfits(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|between:2000,2050',
                'guest_id' => 'required|string'
            ]);

            $month = $request->input('month');
            $year = $request->input('year');
            $guestId = $request->input('guest_id');

            // IMPORTANT: Changed this comparison to use the guest_id from session
            // instead of session()->getId() which was causing the 403 error
            if ($guestId !== session()->get('guest_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid session. Please refresh the page and try again.'
                ], 403);
            }

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // First, clear existing entries for this month
            Planner::where('guest_id', $guestId)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->delete();

            // Then generate new outfits - Call the private helper method now
            $this->generateOutfitsForMonth($guestId, $startDate, $endDate);

            // Count of generated outfits
            $generatedCount = Planner::where('guest_id', $guestId)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->count();

            return response()->json([
                'success' => true,
                'message' => "Successfully generated {$generatedCount} outfits for {$startDate->format('F Y')}",
                'count' => $generatedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error generating monthly outfits: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating outfits: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private helper method to generate outfits for a month
     * This is the implementation of the logic, separated from the HTTP endpoint
     */
    private function generateOutfitsForMonth($guestId, $startDate, $endDate)
    {
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->dayOfWeek;

            $category = 'casual';

            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                // Weekdays (Monday=1 to Friday=5)
                $category = ($dayOfWeek % 2 == 0) ? 'business' : 'formal';
            } elseif ($dayOfWeek == 6) {
                // Saturday
                $category = 'casual';
            } else {
                // Sunday (0)
                $category = 'sportswear';
            }

            // Get clothes by category
            $clothes = $this->getClothesByCategoryAndType($guestId, $category, true);

            // Create new outfit
            $outfit = $this->createOutfit($guestId, $category, $clothes);

            // Create planner entry
            Planner::create([
                'guest_id' => $guestId,
                'date' => $currentDate->toDateString(),
                'outfit_id' => $outfit->id,
                'occasion' => ucfirst($category) . ' day',
                'notes' => 'Auto-generated ' . $category . ' outfit'
            ]);

            $currentDate->addDay();
        }
    }

    /**
     * Get clothes by category and separate them by type
     * Can optionally include clothes from other categories to mix and match
     */
    private function getClothesByCategoryAndType($guestId, $category, $mixCategories = false)
    {
        $topQuery = Cloth::where('type', 'top')
            ->where(function ($query) use ($guestId) {
                $query->where('guest_id', $guestId)
                    ->orWhereNull('guest_id'); // Include system defaults
            });

        $bottomQuery = Cloth::where('type', 'bottom')
            ->where(function ($query) use ($guestId) {
                $query->where('guest_id', $guestId)
                    ->orWhereNull('guest_id'); // Include system defaults
            });

        if (!$mixCategories) {
            // Only use clothes from specified category
            $topQuery->where('category', $category);
            $bottomQuery->where('category', $category);
        } else {
            // Prioritize the main category but also include other categories
            // This creates a weighted selection where the primary category is preferred
            $topQuery->where(function ($query) use ($category) {
                $query->where('category', $category)
                    ->orWhereNotNull('category'); // Include all categories as fallback
            });

            $bottomQuery->where(function ($query) use ($category) {
                $query->where('category', $category)
                    ->orWhereNotNull('category'); // Include all categories as fallback
            });

            // Order by making the requested category come first
            $topQuery->orderByRaw("CASE WHEN category = ? THEN 0 ELSE 1 END", [$category]);
            $bottomQuery->orderByRaw("CASE WHEN category = ? THEN 0 ELSE 1 END", [$category]);
        }

        return [
            'tops' => $topQuery->get(),
            'bottoms' => $bottomQuery->get()
        ];
    }

    /**
     * Generate a new outfit for a specific date
     */
    public function regenerateOutfit(Request $request)
    {
        $date = $request->input('date');
        $mixCategories = $request->input('mix_categories', true);
        
        // FIX: Use consistent guest_id
        $guestId = session()->get('guest_id');
    
        // Find existing planner entry
        $planner = Planner::where('guest_id', $guestId)
            ->where('date', $date)
            ->first();

        // Find existing planner entry
        $planner = Planner::where('guest_id', $guestId)
            ->where('date', $date)
            ->first();

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        // Default to casual
        $category = 'casual';

        // Same logic as in generateMonthlyOutfits
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            // Weekdays
            $category = ($dayOfWeek % 2 == 0) ? 'business' : 'formal';
        } elseif ($dayOfWeek == 6) {
            // Saturday
            $category = 'casual';
        } else {
            // Sunday
            $category = 'sportswear';
        }

        // Override with request category if provided
        if ($request->has('category')) {
            $category = $request->input('category');
        }

        // Get clothes by category
        $clothes = $this->getClothesByCategoryAndType($guestId, $category, $mixCategories);

        // Create new outfit
        $outfit = $this->createOutfit($guestId, $category, $clothes);

        // Update or create planner entry
        if (!$planner) {
            $planner = new Planner();
            $planner->guest_id = $guestId;
            $planner->date = $date;
        }

        // If there was an old outfit, we could delete it here
        // Uncomment if you want this behavior
        /*
        if ($planner->outfit_id) {
            Outfit::find($planner->outfit_id)->delete();
        }
        */

        $planner->outfit_id = $outfit->id;
        $planner->occasion = ucfirst($category) . ' day';
        $planner->notes = 'Auto-generated ' . $category . ' outfit';
        $planner->save();

        // Return full planner entry with outfit and clothes
        return response()->json(
            Planner::with(['outfit.clothes'])
                ->where('id', $planner->id)
                ->first()
        );
    }

    /**
     * Create a new outfit with top and bottom from specified category
     * This version randomly selects from all available clothing items
     */
    private function createOutfit($guestId, $category, $clothes)
{
    // Get all clothing IDs by type
    $topIds = $clothes['tops']->pluck('id')->toArray();
    $bottomIds = $clothes['bottoms']->pluck('id')->toArray();

    // Shuffle to get random order
    shuffle($topIds);
    shuffle($bottomIds);

    // Create new outfit
    $outfit = Outfit::create([
        'guest_id' => $guestId,
        'name' => ucfirst($category) . ' Outfit ' . now()->format('Y-m-d-H-i-s'),
        'is_generated' => true,
    ]);

    // Choose 1-2 tops
    $numTops = rand(1, min(2, count($topIds)));
    $selectedTops = array_slice($topIds, 0, $numTops);

    // Choose 1 bottom (assuming always 1 bottom is enough)
    $selectedBottom = !empty($bottomIds) ? [$bottomIds[0]] : [];

    // Merge selected clothing item IDs
    $selectedClothingIds = array_merge($selectedTops, $selectedBottom);

    // Attach clothes to the outfit
    $outfit->clothes()->attach($selectedClothingIds);

    return $outfit;
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function save(Request $request)
    {
        try {
            $planner = Planner::firstOrCreate([
                'date' => $request->input('date'),
                'guest_id' => $request->input('guest_id'),
            ]);

            // Create outfit if it doesn't exist
            if (!$planner->outfit_id || !$planner->outfit) {
                $outfit = Outfit::create([
                    'guest_id' => $planner->guest_id,
                    'name' => $request->input('name', 'Custom Outfit'),
                    'is_generated' => false,
                ]);
                $planner->outfit_id = $outfit->id;
                $planner->save();
            } else {
                // Update existing outfit name
                $planner->outfit->name = $request->input('name', $planner->outfit->name);
                $planner->outfit->save();
            }

            // Save occasion if provided (from the first occasion entry)
            if (!empty($request->input('occasions')) && isset($request->input('occasions')[0])) {
                $planner->occasion = $request->input('occasions')[0];
                $planner->save();
            }

            // Update existing clothes
            $clothIds = $request->input('clothing_id', []);
            $clothNames = $request->input('cloth_names', []);
            $clothImages = $request->file('cloth_images', []);

            foreach ($clothIds as $index => $id) {
                $cloth = Cloth::find($id);
                if ($cloth) {
                    $cloth->name = $clothNames[$index] ?? $cloth->name;
                    
                    if (isset($clothImages[$index])) {
                        $path = $clothImages[$index]->store('outfits', 'public');
                        $cloth->image_url = 'storage/' . $path;
                    }

                    $cloth->save();
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $type = $request->input('new_image_type', 'unknown');
                $category = $request->input('new_image_category', 'unknown');
                
                foreach ($request->file('images') as $image) {
                    $subPath = "clothes/{$type}"; // e.g., clothes/top
                    $storedPath = $image->store($subPath, 'public');

                    $planner->outfit->clothes()->create([
                        'guest_id' => $planner->guest_id,
                        'name' => 'New ' . ucfirst($type),
                        'image_url' => 'storage/' . $storedPath,
                        'type' => $type,
                        'category' => $category,
                    ]);
                }
            }

            // Fetch the updated planner entry with relationships for response
            $updatedPlanner = Planner::with(['outfit.clothes'])
                ->where('id', $planner->id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Outfit saved successfully',
                'plannerEntry' => $updatedPlanner
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error saving outfit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving outfit',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}