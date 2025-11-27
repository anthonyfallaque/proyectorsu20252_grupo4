<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
    'status' => 1
];

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    public function activities()
    {
        return $this->hasMany(MaintenanceActivity::class, 'schedule_id');
    }
}
