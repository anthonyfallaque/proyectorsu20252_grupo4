<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceActivity extends Model
{
    use HasFactory;

    protected $guarded = [];

    // AGREGAR ESTO:
    protected $casts = [
        'completed' => 'integer',  // O 'boolean' si prefieres
    ];

    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'schedule_id');
    }
}
