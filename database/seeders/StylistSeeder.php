<?php

namespace Database\Seeders;

use App\Models\Stylist;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StylistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stylist::create([
            'name' => 'Alexandra Hart',
            'email' => 'alexandra@example.com',
            'bio' => 'Experienced fashion stylist specializing in modern, minimalist looks.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Julian Lee',
            'email' => 'julian@example.com',
            'bio' => 'Streetwear enthusiast with a keen eye for urban fashion trends.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Camila Torres',
            'email' => 'camila@example.com',
            'bio' => 'Latin-inspired stylist with a flair for bold patterns and vibrant colors.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Marcus Bennett',
            'email' => 'marcus@example.com',
            'bio' => 'Men’s fashion expert who blends classic tailoring with modern design.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Sophie Nguyen',
            'email' => 'sophie@example.com',
            'bio' => 'Minimalist stylist focused on clean lines and elegant simplicity.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Dante Morales',
            'email' => 'dante@example.com',
            'bio' => 'Fashion-forward stylist embracing street and high fashion crossovers.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Isabella Chen',
            'email' => 'isabella@example.com',
            'bio' => 'Expert in East-Asian inspired fashion and contemporary fusion styles.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Liam Patel',
            'email' => 'liam@example.com',
            'bio' => 'Focuses on sustainable and ethical fashion for everyday wear.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Nina Kowalski',
            'email' => 'nina@example.com',
            'bio' => 'Known for artistic styling, mixing vintage with futuristic elements.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);

        Stylist::create([
            'name' => 'Ezra Johnson',
            'email' => 'ezra@example.com',
            'bio' => 'Celebrity stylist who curates bold, trend-setting red carpet outfits.',
            'profile_image' => 'images/photo.jpg',
            'link' => 'https://wa.link/yg76v1',
        ]);
    }
}
