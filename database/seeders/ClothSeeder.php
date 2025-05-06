<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cloth;
use File;

class ClothSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guestId = session()->getId(); // For guest users

        $topPath = public_path('images/clothes/top');
        $bottomPath = public_path('images/clothes/bottom');

        $topImages = File::files($topPath);
        $bottomImages = File::files($bottomPath);

        foreach ($topImages as $image) {
            Cloth::create([
                'name' => pathinfo($image, PATHINFO_FILENAME),
                'category' => 'casual',
                'color' => 'black',
                'season' => 'summer',
                'image_url' => 'images/clothes/top/' . $image->getFilename(),
                'guest_id' => $guestId,
            ]);
        }

        foreach ($bottomImages as $image) {
            Cloth::create([
                'name' => pathinfo($image, PATHINFO_FILENAME),
                'category' => 'casual',
                'color' => 'blue',
                'season' => 'summer',
                'image_url' => 'images/clothes/bottom/' . $image->getFilename(),
                'guest_id' => $guestId,
            ]);
        }    
    }
}
