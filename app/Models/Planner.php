<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Outfit;

class Planner extends Model
{
    use HasFactory;
    protected $table = 'planner_entries';

    protected $fillable = [
        'user_id',     
        'outfit_id',
        'date',
        'occasion',
        'notes',
        'guest_id',     
    ];

    public function outfit(){
        return $this->belongsTo(Outfit::class);
    }

}
