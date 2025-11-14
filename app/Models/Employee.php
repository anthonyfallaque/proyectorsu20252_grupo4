<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'lastnames',
        'names',
        'birthday',
        'license',
        'address',
        'email',
        'photo',
        'phone',
        'status',
        'password',
        'type_id',
        'position_id'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function getFullNameAttribute()
    {
        return $this->names . ' ' . $this->lastnames;
    }

    public function position()
    {
        return $this->belongsTo(EmployeeType::class, 'type_id', 'id');
    }
    public function vacations()
    {
        return $this->hasMany(Vacation::class);
    }

    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class, 'type_id', 'id');
    }

    public function groupDetails()
    {
        return $this->hasMany(Groupdetail::class);
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
