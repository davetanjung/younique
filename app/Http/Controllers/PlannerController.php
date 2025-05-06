<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Planner;
use App\Models\Outfit;
use App\Models\Cloth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentDate = date('m-d');
        $guestId = session()->getId();
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

        $guestId = session()->getId();

        // Check if planner entries exist for this month and guest
        $existingEntries = Planner::where('guest_id', $guestId)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        // If no entries exist, generate them
        if ($existingEntries === 0) {
            $this->generateMonthlyOutfits($guestId, $startDate, $endDate);
        }

        $plannerEntries = Planner::with(['outfit.clothes'])
            ->where('guest_id', $guestId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return response()->json($plannerEntries);
    }

    /**
     * Generate outfit combinations by category for the whole month
     * Randomly mixes clothing items to create varied outfit combinations
     */
    private function generateMonthlyOutfits($guestId, $startDate, $endDate)
    {
        // Get all clothes by category for this guest - with mixing enabled
        $casual = $this->getClothesByCategoryAndType($guestId, 'casual', true);
        $formal = $this->getClothesByCategoryAndType($guestId, 'formal', true);
        $sportswear = $this->getClothesByCategoryAndType($guestId, 'sportswear', true);
        $business = $this->getClothesByCategoryAndType($guestId, 'business', true);
        $nightwear = $this->getClothesByCategoryAndType($guestId, 'nightwear', true);

        // Get all available clothes for random mixing
        $allTops = Cloth::where('type', 'top')
            ->where(function ($query) use ($guestId) {
                $query->where('guest_id', $guestId)
                    ->orWhereNull('guest_id');
            })
            ->get();

        $allBottoms = Cloth::where('type', 'bottom')
            ->where(function ($query) use ($guestId) {
                $query->where('guest_id', $guestId)
                    ->orWhereNull('guest_id');
            })
            ->get();

        // Prepare clothesByCategory with options for mixing
        $clothesByCategory = [
            'casual' => $casual,
            'formal' => $formal,
            'sportswear' => $sportswear,
            'business' => $business,
            'nightwear' => $nightwear,
            // Add a mixed category that combines all clothing items
            'mixed' => [
                'tops' => $allTops,
                'bottoms' => $allBottoms
            ]
        ];

        // Track used combinations to avoid repeats
        $usedCombinations = [];

        // Generate outfits for each day of the month
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Determine category based on day of week
            $dayOfWeek = $currentDate->dayOfWeek;

            // Monday-Friday: Business/Formal, Saturday: Casual, Sunday: Sportswear
            $category = 'casual'; // Default
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

            // If it's last day of month, use nightwear
            if ($currentDate->day === $endDate->day) {
                $category = 'nightwear';
            }

            // Every few days, use completely mixed outfits regardless of category
            if ($currentDate->day % 5 == 0) {
                $category = 'mixed';
            }

            // Determine which clothes collection to use
            $clothesCollection = $clothesByCategory[$category];

            // Check if we can create a valid outfit
            if (
                !empty($clothesCollection['tops']) &&
                !empty($clothesCollection['bottoms'])
            ) {
                // Generate a unique outfit for this date
                $outfit = $this->createOutfit($guestId, $category, $clothesCollection);

                // Create planner entry
                Planner::updateOrCreate(
                    ['guest_id' => $guestId, 'date' => $currentDate->format('Y-m-d')],
                    [
                        'outfit_id' => $outfit->id,
                        'occasion' => $category === 'mixed' ? 'Mixed Style' : ucfirst($category) . ' day',
                        'notes' => 'Auto-generated ' . $category . ' outfit'
                    ]
                );
            }

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
        $guestId = session()->getId();

        // Find existing planner entry
        $planner = Planner::where('guest_id', $guestId)
            ->where('date', $date)
            ->first();

        // Determine category based on day of week
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

        // Choose 1-2 bottoms
        $numBottoms = rand(1, min(2, count($bottomIds)));
        $selectedBottoms = array_slice($bottomIds, 0, $numBottoms);

        // Prepare all clothing items to add
        $clothingOutfitRows = [];

        foreach ($selectedTops as $topId) {
            $clothingOutfitRows[] = [
                'outfit_id' => $outfit->id,
                'clothing_id' => $topId
            ];
        }

        foreach ($selectedBottoms as $bottomId) {
            $clothingOutfitRows[] = [
                'outfit_id' => $outfit->id,
                'clothing_id' => $bottomId
            ];
        }

        // Add clothes to outfit
        DB::table('clothing_outfits')->insert($clothingOutfitRows);

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
        $planner = Planner::firstOrCreate([
            'date' => $request->input('date'),
            'guest_id' => $request->input('guest_id'),
        ]);

        // Update existing clothes
        $clothIds = $request->input('cloth_ids', []);
        $clothNames = $request->input('cloth_names', []);
        $occasions = $request->input('occasions', []);
        $clothImages = $request->file('cloth_images', []);

        foreach ($clothIds as $index => $id) {
            $cloth = Cloth::find($id);
            if ($cloth && $cloth->outfit_id === $planner->outfit->id) {
                $cloth->name = $clothNames[$index] ?? $cloth->name;
                $planner->occasion = $occasions[$index] ?? $planner->occasion;

                if (isset($clothImages[$index])) {
                    $path = $clothImages[$index]->store('outfits', 'public');
                    $cloth->image_url = 'storage/' . $path;
                }

                $cloth->save();
            }
        }

        // Handle new clothes (if using modalImagesInput)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('clothes', 'public');
                $planner->outfit()->create([
                    'image_url' => 'storage/' . $path,
                    'name' => 'New Cloth',
                    'occasion' => 'General',
                ]);
            }
        }

        return response()->json(['success' => true]);
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