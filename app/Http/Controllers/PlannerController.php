<?php

namespace App\Http\Controllers;

use Auth;
use DB; // Keep if your save() method uses it.
use Illuminate\Http\Request;
use App\Models\Planner;
use App\Models\Outfit;
use App\Models\Cloth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
// use GuzzleHttp\Client as GuzzleClient; // Only if you prefer direct Guzzle over Http facade
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException; // For more specific exception handling


class PlannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $currentDate = date('m-d'); // Not used in view
        $userId = Auth::check() ? Auth::id() : null;
        $guestId = session()->get('guest_id');

        if (!$userId && !$guestId) {
            $guestId = Str::uuid()->toString();
            session()->put('guest_id', $guestId);
        }

        return view('planner.index', compact('guestId'));
    }

    public function getMonthlyEntries(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $guestId = session()->get('guest_id');

        if (!$guestId) {
            Log::warning('getMonthlyEntries called without guestId in session.');
            return response()->json(['error' => 'Guest session not found.'], 400);
        }

        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // --- REMOVED AUTO-GENERATION ---
            // $existingEntriesCount = Planner::where('guest_id', $guestId)
            //     ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            //     ->count();
            // if ($existingEntriesCount === 0) {
            //     Log::info("No existing entries for {$guestId} for {$startDate->format('F Y')}. NOT auto-generating. User must click button.");
            //     // $this->generateOutfitsForMonth($guestId, $startDate, $endDate); // DO NOT CALL THIS HERE
            // }
            // --- END REMOVED AUTO-GENERATION ---

            $plannerEntries = Planner::with(['outfit.clothes'])
                ->where('guest_id', $guestId)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->date)->toDateString();
                });

            return response()->json($plannerEntries);

        } catch (\Exception $e) {
            Log::error("Error in getMonthlyEntries for guest {$guestId}, month {$month}-{$year}: " . $e->getMessage());
            // Return an empty object or an error structure your JS can handle for "failed to load"
            return response()->json([], 500); // Or ['error' => 'Failed to load calendar data.']
        }
    }

    public function getAvailableClothes(Request $request)
    {
        // $request->validate([ // Validation is good, but ensure it aligns with how guest_id is passed
        //     'guest_id' => 'sometimes|string|uuid'
        // ]);
        $guestId = $request->input('guest_id', session()->get('guest_id'));

        if (!$guestId) {
            return response()->json(['error' => 'Guest ID is required.'], 400);
        }

        try {
            $clothes = Cloth::where(function ($query) use ($guestId) {
                $query->where('guest_id', $guestId)
                    ->orWhereNull('guest_id'); // Include system-default clothes
            })
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($clothes);
        } catch (\Exception $e) {
            Log::error('Error fetching available clothes for guest ' . $guestId . ': ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve clothes. Please try again.'], 500);
        }
    }

    /**
     * Handle the request to generate monthly outfits (HTTP endpoint called by frontend button).
     */
    public function generateMonthlyOutfits(Request $request) // This is the public endpoint
    {
        ini_set('max_execution_time', 300);
        try {
            $validated = $request->validate([
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|between:2000,2050',
                'guest_id' => 'required|string|uuid'
            ]);

            $month = $validated['month'];
            $year = $validated['year'];
            $guestIdFromRequest = $validated['guest_id']; // This comes from JS
            $guestIdFromSession = session()->get('guest_id');

            if ($guestIdFromRequest !== $guestIdFromSession) {
                Log::warning("Session mismatch during outfit generation. Request: {$guestIdFromRequest}, Session: {$guestIdFromSession}");
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid session. Please refresh the page and try again.'
                ], 403); // 403 Forbidden
            }

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            Log::info("User {$guestIdFromSession} triggered outfit generation for {$startDate->format('F Y')}. Clearing old entries if any.");
            // Clear existing planner entries for this specific guest and month before generating new ones
            Planner::where('guest_id', $guestIdFromSession)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->delete(); // This also means previously saved manual outfits for these days will be gone. Consider if this is desired.

            // Call the private helper method to do the actual generation
            $this->doGenerateOutfitsForMonth($guestIdFromSession, $startDate, $endDate);

            // Fetch the newly generated entries to return them
            $generatedEntries = Planner::with('outfit.clothes')
                ->where('guest_id', $guestIdFromSession)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orderBy('date', 'asc')
                ->get();

            $generatedCount = $generatedEntries->filter(function ($entry) {
                return $entry->outfit_id !== null; // Count entries that actually have an outfit
            })->count();

            return response()->json([
                'success' => true,
                'message' => "Successfully processed outfit generation for {$startDate->format('F Y')}. {$generatedCount} outfits created/attempted.",
                'count' => $generatedCount,
                'entries' => $generatedEntries, // Send all entries, even if some days have no outfit
            ]);

        } catch (ValidationException $e) {
            Log::error('Validation error generating monthly outfits: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid input for generating outfits.',
                'errors' => $e->errors()
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            Log::error('Error in generateMonthlyOutfits endpoint: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating outfits: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private helper method to generate an outfit using AI.
     */
    private function generateOutfitWithAI($guestId, $occasion, $dateContext, $allClothesForGuest)
    {
        if ($allClothesForGuest->isEmpty()) {
            Log::info("generateOutfitWithAI called for guest {$guestId} but no clothes were provided.");
            return null;
        }

        $clothesDataForAI = $allClothesForGuest->map(function ($cloth) {
            return [
                'id' => $cloth->id,
                'name' => $cloth->name,
                'type' => $cloth->type,
                'color' => $cloth->color,
                'category' => $cloth->category,
                'season' => $cloth->season,
            ];
        })->toArray();

        try {
            $aiOutfitMatcherUrl = env('AI_OUTFIT_MATCHER_URL', 'http://localhost:5003/match-outfit');
            $aiOutfitMatcherKey = env('AI_OUTFIT_MATCHER_KEY');

            Log::info("Calling AI Outfit Matcher for guest {$guestId}, occasion '{$occasion}'. URL: {$aiOutfitMatcherUrl}");

            $headers = ['Accept' => 'application/json'];
            if ($aiOutfitMatcherKey) {
                $headers['X-API-Key'] = $aiOutfitMatcherKey;
            }

            // Add debug logging of the request payload
            Log::debug('AI Request Payload', [
                'clothes_count' => count($clothesDataForAI),
                'occasion' => $occasion,
                'context' => $dateContext
            ]);

            $response = Http::timeout(60)
                ->withHeaders($headers)
                ->post($aiOutfitMatcherUrl, [
                    'clothes_list' => $clothesDataForAI,
                    'occasion' => $occasion,
                    'context' => $dateContext
                ]);

            if (!$response->successful()) {
                Log::error("AI Outfit Matcher service request failed. Status: " . $response->status() . ". Body: " . $response->body());
                return null;
            }

            $result = $response->json();
            Log::debug('AI Response', ['response' => $result]); // Debug logging

            if (isset($result['selected_ids'])) {
                // Remove duplicate ID processing (you had two different approaches)
                $selectedClothIds = [];
                foreach ((array) $result['selected_ids'] as $id) {
                    $id = is_string($id) ? trim($id) : $id; // Handle string IDs
                    if (is_numeric($id)) {
                        $selectedClothIds[] = (int) $id;
                    }
                }

                if (empty($selectedClothIds)) {
                    Log::info("AI returned empty/invalid selected_ids for guest {$guestId}");
                    return null;
                }

                // Remove this redundant line - you already processed the IDs above
                // $selectedClothIds = array_filter($result['selected_ids'], 'is_int');

                $outfit = Outfit::create([
                    'guest_id' => $guestId,
                    'name' => ucfirst($occasion) . ' AI Outfit ' . now()->format('Y-m-d H:i:s'),
                    'is_generated' => true,
                ]);

                $validClothes = Cloth::where(function ($query) use ($guestId) {
                    $query->where('guest_id', $guestId)
                        ->orWhereNull('guest_id');
                })
                    ->whereIn('id', $selectedClothIds)
                    ->get();

                if ($validClothes->isEmpty()) {
                    Log::warning("No valid clothes found for selected IDs. Deleting outfit {$outfit->id}.");
                    $outfit->delete();
                    return null;
                }

                $outfit->clothes()->attach($validClothes->pluck('id')->toArray());
                Log::info("Created AI outfit {$outfit->id} with " . $validClothes->count() . " items");
                return $outfit;

            } else {
                Log::error('AI response missing selected_ids', ['response' => $result]);
                return null;
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection error calling AI service: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("Outfit generation failed: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Private helper method that contains the loop for generating outfits for a month.
     * Renamed from generateOutfitsForMonth to avoid confusion with the public endpoint.
     */
    private function doGenerateOutfitsForMonth($guestId, Carbon $startDate, Carbon $endDate)
    {
        $currentDate = $startDate->copy();
        Log::info("Starting actual outfit generation loop for guest {$guestId} from {$startDate->toDateString()} to {$endDate->toDateString()}.");

        $allClothesForGuest = Cloth::where(function ($query) use ($guestId) {
            $query->where('guest_id', $guestId)
                ->orWhereNull('guest_id');
        })->get();

        if ($allClothesForGuest->count() < 1) {
            Log::warning("Not enough clothes (0) for guest {$guestId} to generate AI outfits. Aborting month generation.");
            // Create empty planner entries for the month so the calendar doesn't try to regenerate again
            $tempDate = $startDate->copy();
            while ($tempDate <= $endDate) {
                Planner::firstOrCreate(
                    ['guest_id' => $guestId, 'date' => $tempDate->toDateString()],
                    ['outfit_id' => null, 'occasion' => 'No outfit data', 'notes' => 'Wardrobe empty']
                );
                $tempDate->addDay();
            }
            return;
        }

        $daysInMonth = $startDate->daysInMonth;
        $dayCounter = 0;

        while ($currentDate <= $endDate) {
            DB::beginTransaction(); // Start transaction for each day's outfit creation
            try {
                $dayCounter++;
                $dayOfWeek = $currentDate->dayOfWeekIso;

                $category = 'casual';
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    $category = ($dayOfWeek % 2 == 0) ? 'business' : 'formal';
                } elseif ($dayOfWeek == 6) {
                    $category = 'party';
                } else {
                    $category = 'sportswear';
                }

                $dateContext = "Outfit for " . $currentDate->format('l, F jS, Y') . ". The determined category for the day is '{$category}'. The season is likely " . $this->getSeasonForDate($currentDate) . ".";

                Log::info("Attempting to generate outfit for day {$dayCounter}/{$daysInMonth}: {$currentDate->toDateString()}, Guest: {$guestId}, Category: {$category}");
                $outfit = $this->generateOutfitWithAI($guestId, $category, $dateContext, $allClothesForGuest);

                $plannerEntryData = [
                    'outfit_id' => null, // Default to null
                    'occasion' => ucfirst($category) . ' Day',
                    'notes' => 'No outfit generated'
                ];

                if ($outfit) {
                    $plannerEntryData['outfit_id'] = $outfit->id;
                    $plannerEntryData['notes'] = 'AI-generated ' . $category . ' outfit';
                } else {
                    Log::warning("AI generation failed for {$guestId} on {$currentDate->toDateString()}. Attempting random fallback.");
                    $randomClothes = $this->getClothesByCategoryAndType($guestId, $category, true);
                    $canCreateRandom = ($randomClothes['tops']->where('type', 'dress')->isNotEmpty()) ||
                        ($randomClothes['tops']->where('type', 'top')->isNotEmpty() && $randomClothes['bottoms']->isNotEmpty());

                    if ($canCreateRandom) {
                        $fallbackOutfit = $this->createOutfit($guestId, $category, $randomClothes);
                        if ($fallbackOutfit) {
                            $plannerEntryData['outfit_id'] = $fallbackOutfit->id;
                            $plannerEntryData['notes'] = 'Random fallback ' . $category . ' outfit';
                            Log::info("Created random fallback outfit for {$guestId} on {$currentDate->toDateString()}");
                        } else {
                            Log::warning("Random fallback outfit creation also failed for {$guestId} on {$currentDate->toDateString()}");
                            $plannerEntryData['notes'] = 'Fallback generation failed';
                        }
                    } else {
                        Log::info("No outfit (AI or fallback) generated due to insufficient clothes for {$category} for {$guestId} on {$currentDate->toDateString()}.");
                        $plannerEntryData['notes'] = 'Insufficient clothes for category';
                    }
                }

                // Create or update planner entry. Using firstOrCreate ensures an entry for each day.
                Planner::updateOrCreate(
                    ['guest_id' => $guestId, 'date' => $currentDate->toDateString()],
                    $plannerEntryData
                );
                DB::commit(); // Commit successful day generation
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback if error for this day
                Log::error("Error processing day {$currentDate->toDateString()} for guest {$guestId}: " . $e->getMessage());
                // Optionally create an empty planner entry with error note
                Planner::firstOrCreate(
                    ['guest_id' => $guestId, 'date' => $currentDate->toDateString()],
                    ['outfit_id' => null, 'occasion' => 'Generation Error', 'notes' => 'Error during generation']
                );
            }


            $currentDate->addDay();

            if ($currentDate <= $endDate) {
                $sleepDuration = 5;
                Log::info("Sleeping for {$sleepDuration} seconds to respect API rate limits before processing next day...");
                sleep($sleepDuration);
            }
        }
        Log::info("Finished outfit generation loop for guest {$guestId}.");
    }

    private function getSeasonForDate(Carbon $date)
    {
        $month = $date->month;
        if (in_array($month, [12, 1, 2]))
            return 'Winter';
        if (in_array($month, [3, 4, 5]))
            return 'Spring';
        if (in_array($month, [6, 7, 8]))
            return 'Summer';
        return 'Autumn';
    }

    private function getClothesByCategoryAndType($guestId, $category, $mixCategories = true)
    {
        $topQuery = Cloth::query()
            ->where(fn($q) => $q->where('type', 'top')->orWhere('type', 'dress'))
            ->where(function ($query) use ($guestId) {
                $query->where('guest_id', $guestId)
                    ->orWhereNull('guest_id');
            });

        $bottomQuery = Cloth::query()
            ->where('type', 'bottom')
            ->where(function ($query) use ($guestId) {
                $query->where('guest_id', $guestId)
                    ->orWhereNull('guest_id');
            });

        if (!$mixCategories && $category !== 'any') {
            $topQuery->where('category', $category);
            $bottomQuery->where('category', $category);
        } elseif ($category !== 'any') { // mixCategories is true and category is not 'any'
            $topQuery->orderByRaw("CASE WHEN category = ? THEN 0 ELSE 1 END, RAND()", [$category]);
            $bottomQuery->orderByRaw("CASE WHEN category = ? THEN 0 ELSE 1 END, RAND()", [$category]);
        } else { // mixCategories is true and category is 'any', or just mixCategories is true without specific category preference
            $topQuery->inRandomOrder();
            $bottomQuery->inRandomOrder();
        }
        // Fetch a small sample for random selection if needed
        $tops = $topQuery->limit(10)->get();
        $bottoms = $bottomQuery->limit(10)->get();

        return ['tops' => $tops, 'bottoms' => $bottoms];
    }

    private function createOutfit($guestId, $category, $clothesCollections)
    {
        $topsAndDresses = $clothesCollections['tops'];
        $bottoms = $clothesCollections['bottoms'];

        $selectedTopOrDress = null;
        $availableDresses = $topsAndDresses->where('type', 'dress');
        $availableTops = $topsAndDresses->where('type', 'top');

        if ($availableDresses->isNotEmpty()) {
            $selectedTopOrDress = $availableDresses->random();
        } elseif ($availableTops->isNotEmpty()) {
            $selectedTopOrDress = $availableTops->random();
        }

        $selectedBottom = null;
        if ($selectedTopOrDress && $selectedTopOrDress->type !== 'dress' && $bottoms->isNotEmpty()) {
            $selectedBottom = $bottoms->random();
        }

        if (!$selectedTopOrDress) {
            Log::warning("RANDOM: Could not select a top or dress. Category: {$category}");
            return null;
        }
        if ($selectedTopOrDress->type === 'top' && !$selectedBottom) {
            Log::warning("RANDOM: Selected a top but no bottom available/selected. Category: {$category}");
            // Decide: is a top-only outfit valid for random fallback? For now, let's say no.
            return null;
        }


        $outfit = Outfit::create([
            'guest_id' => $guestId,
            'name' => ucfirst($category) . ' Random Outfit ' . now()->format('Y-m-d H:i:s'),
            'is_generated' => true,
        ]);

        $selectedClothingIds = [];
        if ($selectedTopOrDress)
            $selectedClothingIds[] = $selectedTopOrDress->id;
        if ($selectedBottom)
            $selectedClothingIds[] = $selectedBottom->id;

        if (!empty($selectedClothingIds)) {
            $outfit->clothes()->attach($selectedClothingIds);
        } else {
            Log::warning("RANDOM: Attempted to create outfit with no clothes. Deleting outfit {$outfit->id}.");
            $outfit->delete();
            return null;
        }
        return $outfit;
    }

    public function regenerateOutfit(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'category' => 'sometimes|string|in:casual,formal,sportswear,business,party,any', // 'any' for true random
            'occasion' => 'sometimes|nullable|string|max:255'
        ]);

        $date = $validated['date'];
        $guestId = session()->get('guest_id');

        if (!$guestId) {
            return response()->json(['success' => false, 'message' => 'Guest session not found.'], 400);
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
        $defaultCategory = 'casual';
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            $defaultCategory = ($dayOfWeek % 2 == 0) ? 'business' : 'formal';
        } elseif ($dayOfWeek == 6) {
            $defaultCategory = 'party';
        } else {
            $defaultCategory = 'sportswear';
        }

        $category = $request->input('category', $defaultCategory);
        $notesPrefix = 'AI-regenerated';

        $allClothesForGuest = Cloth::where(function ($query) use ($guestId) {
            $query->where('guest_id', $guestId)->orWhereNull('guest_id');
        })->get();

        if ($allClothesForGuest->count() < 1) {
            return response()->json(['success' => false, 'message' => 'Not enough clothes in wardrobe.'], 400);
        }

        $dateContext = "Outfit for " . Carbon::parse($date)->format('l, F jS, Y') . ". The category for the day is '{$category}'. The season is likely " . $this->getSeasonForDate(Carbon::parse($date)) . ".";

        Log::info("Regenerating outfit for {$guestId} on {$date} with AI. Category: {$category}");
        $newOutfit = $this->generateOutfitWithAI($guestId, $category, $dateContext, $allClothesForGuest);

        if (!$newOutfit) {
            Log::warning("AI regeneration failed for {$guestId} on {$date}. Attempting random fallback with category: {$category}");
            $randomClothes = $this->getClothesByCategoryAndType($guestId, $category, true); // mixCategories true for better chance
            $newOutfit = $this->createOutfit($guestId, $category, $randomClothes);
            if ($newOutfit)
                $notesPrefix = 'Randomly-regenerated';
        }

        if (!$newOutfit) {
            return response()->json(['success' => false, 'message' => 'Failed to regenerate outfit.'], 500);
        }

        $planner = Planner::firstOrNew(
            ['guest_id' => $guestId, 'date' => $date]
        );

        if ($planner->exists && $planner->outfit_id && $planner->outfit_id != $newOutfit->id) {
            $oldOutfit = Outfit::find($planner->outfit_id);
            if ($oldOutfit && $oldOutfit->is_generated) { // Only delete old *generated* outfits
                Log::info("Deleting old generated outfit {$oldOutfit->id} for planner entry {$planner->id}");
                $oldOutfit->delete();
            }
        }

        $planner->outfit_id = $newOutfit->id;
        $planner->occasion = $request->filled('occasion') ? $validated['occasion'] : (ucfirst($category) . ' Day');
        $planner->notes = $notesPrefix . ' ' . $category . ' outfit';
        $planner->save();

        $updatedPlannerEntry = Planner::with(['outfit.clothes'])->findOrFail($planner->id);

        return response()->json([
            'success' => true,
            'message' => 'Outfit regenerated successfully!',
            'plannerEntry' => $updatedPlannerEntry
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'guest_id' => 'required|string|uuid',
            'occasion' => 'nullable|string|max:255',
            'outfit_name' => 'nullable|string|max:255',
            'clothing_ids' => 'present|array',
            'clothing_ids.*' => 'integer|exists:clothes,id',
        ]);

        try {
            DB::beginTransaction();

            $planner = Planner::firstOrCreate(
                ['date' => $validated['date'], 'guest_id' => $validated['guest_id']]
            );

            $outfit = null;
            // If an outfit already exists for this planner day, and it's NOT a generated one,
            // or if no outfit exists, we create/update a manually set outfit.
            // If it's a generated one, clicking "Save" on a modified version should arguably create a NEW manual outfit.
            if ($planner->outfit_id && $planner->outfit && !$planner->outfit->is_generated) {
                $outfit = $planner->outfit; // Update existing manual outfit
            } else {
                // If it was a generated outfit, or no outfit, create a new one for this manual save.
                // This prevents overwriting an AI outfit directly; user saves a "version" of it.
                if ($planner->outfit_id && $planner->outfit && $planner->outfit->is_generated) {
                    Log::info("Manual save for a previously AI-generated outfit. Creating a new manual outfit record.");
                }
                $outfit = Outfit::create([
                    'guest_id' => $planner->guest_id,
                    'name' => $validated['outfit_name'] ?? 'Custom Outfit - ' . $validated['date'],
                    'is_generated' => false, // This is now a manually curated outfit
                ]);
                $planner->outfit_id = $outfit->id; // Link new/updated outfit to planner
            }

            // Update outfit name if provided, or use existing, or default
            $outfit->name = $validated['outfit_name'] ?? $outfit->name ?? ('Custom Outfit - ' . $validated['date']);
            $outfit->is_generated = false; // Mark as manually handled
            $outfit->save();

            $planner->occasion = $validated['occasion'] ?? $planner->occasion;
            $planner->notes = "Manually saved outfit"; // Update notes
            $planner->save();

            $outfit->clothes()->sync($validated['clothing_ids']);

            DB::commit();

            $updatedPlanner = Planner::with(['outfit.clothes'])->findOrFail($planner->id);

            return response()->json([
                'success' => true,
                'message' => 'Outfit saved successfully!',
                'plannerEntry' => $updatedPlanner
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error saving outfit: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving outfit: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the outfit. Please try again.'
            ], 500);
        }
    }
}