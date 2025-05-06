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
    ];    
}
