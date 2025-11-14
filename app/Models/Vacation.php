<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'request_date',
        'requested_days',
        'end_date',
        'available_days',
        'status',
        'notes'
    ];

    protected $casts = [
        'request_date' => 'date',
        'end_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getFullNameAttribute()
    {
        return $this->employee->names . ' ' . $this->employee->lastnames;
    }
}