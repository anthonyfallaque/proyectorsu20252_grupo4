<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'color_id');
    }
}
