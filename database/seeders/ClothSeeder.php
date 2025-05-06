<?php

namespace Database\Seeders;

use Auth;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cloth;
use File;
use Str;

class ClothSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = Auth::check() ? Auth::id() : null;
        $guestId = session()->get('guest_id');

        if (!$userId && !$guestId) {
            $guestId = Str::uuid()->toString();
            session()->put('guest_id', $guestId);
        }

        $topPath = public_path('/storage/clothes/top');
        $bottomPath = public_path('/storage/clothes/bottom');

        $topImages = File::files($topPath);
        $bottomImages = File::files($bottomPath);

        foreach ($topImages as $image) {
            Cloth::create([
                'name' => pathinfo($image, PATHINFO_FILENAME),
                'category' => 'casual',
                'color' => 'black',
                'season' => 'summer',
                'image_url' => '/storage/clothes/top/' . $image->getFilename(),
                'guest_id' => $guestId,
            ]);
        }

        foreach ($bottomImages as $image) {
            Cloth::create([
                'name' => pathinfo($image, PATHINFO_FILENAME),
                'category' => 'casual',
                'color' => 'blue',
                'season' => 'summer',
                'image_url' => '/storage/clothes/bottom/' . $image->getFilename(),
                'guest_id' => $guestId,
            ]);
        }    
    }
}
