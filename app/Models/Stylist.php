<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'bio',
        'profile_image', 
        'link'       
    ];
    // wa.link/yg76v1
}
