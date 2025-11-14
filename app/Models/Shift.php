<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'hour_in',    
        'hour_out'    
    ];

    public function employeeGroups()
    {
        return $this->hasMany(EmployeeGroup::class, 'shift_id');
    }

    public function changes()
    {
        return $this->hasMany(Change::class, 'shift_id');
    }

    public function getDisplayInfoAttribute()
    {
        return $this->description ?? 'Sin descripción';
    }

    public function getFullScheduleAttribute()
    {
        return $this->hour_in . ' - ' . $this->hour_out;
    }
}