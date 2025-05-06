<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Outfit;
use App\Models\Planner;
use App\Models\Cloth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guestId = session()->getId();

        $tops = Cloth::where('type', 'top')->get();
        $bottoms = Cloth::where('type', 'bottom')->get();

        if ($tops->isEmpty() || $bottoms->isEmpty()) {
            $this->command->warn('Not enough tops or bottoms to generate outfits.');
            return;
        }

        $outfits = collect();

        for ($i = 0; $i < 5; $i++) {
            // $top = $tops->random();
            // $bottom = $bottoms->random();

            $outfit = Outfit::create([
                'guest_id' => $guestId,
                'name' => 'Guest Outfit ' . ($i + 1),
                'is_generated' => true,
            ]);

            // DB::table('clothing_outfits')->insert([
            //     ['outfit_id' => $outfit->id, 'clothing_id' => $top->id],
            //     ['outfit_id' => $outfit->id, 'clothing_id' => $bottom->id],
            // ]);

            $outfits->push($outfit);
        }

        // Assign outfits to planner
        $currentMonth = Carbon::now()->startOfMonth();
        $daysInMonth = $currentMonth->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentMonth->copy()->day($day);
            $randomOutfit = $outfits->random();

            Planner::updateOrCreate(
                ['guest_id' => 'demo_guest', 'date' => $date],
                [
                    'outfit_id' => $randomOutfit->id,
                    'occasion' => 'Auto-generated',
                    'notes' => 'Mix & matched outfit for the day.',
                ]
            );
        }
    }
}
