<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'average_waste',
        'status'
    ];


    public function coords()
    {
        return $this->hasMany(Coord::class, 'zone_id');
    }


    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employeeGroups()
    {
        return $this->hasMany(Employeegroup::class, 'zone_id');
    }

    public function schedulings()
    {
        return $this->hasManyThrough(Scheduling::class, Employeegroup::class, 'zone_id', 'group_id');
    }
}
