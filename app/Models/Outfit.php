<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Cloth;

class Outfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'is_generated',
        'guest_id',
    ];

    public function clothes()
    {
        return $this->belongsToMany(Cloth::class, 'clothing_outfits', 'outfit_id', 'clothing_id');
    }
    public function plannerEntries()
    {
        return $this->hasMany(Planner::class);
    }

}
