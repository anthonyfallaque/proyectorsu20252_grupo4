<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function schedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    protected $attributes = [
    'status' => 1
];
}
