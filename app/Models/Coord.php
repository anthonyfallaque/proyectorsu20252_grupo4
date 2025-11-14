<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coord extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}