<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cloth extends Model
{
    use HasFactory;
    protected $table = 'clothes';

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'color',
        'season',
        'image_url',
        'guest_id',
        'type'
    ];    
    
    public function outfits()
    {
        return $this->belongsToMany(Outfit::class, 'clothing_outfits', 'clothing_id', 'outfit_id')
                    ->withTimestamps(); // Optional: if your 'clothing_outfits' pivot table has created_at and updated_at columns
    }
}
