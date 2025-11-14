<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\EmployeeType;

class Employeegroup extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function configgroup()
    {
        return $this->hasMany(Configgroup::class);
    }

    public function conductors() {
        $conductorId = EmployeeType::whereRaw('LOWER(name) = ?', ['conductor'])->first()->id;
        return $this->belongsToMany(Employee::class, 'configgroups')
                    ->where('type_id', $conductorId);
    }
    
    public function helpers() {
        $helperId = EmployeeType::whereRaw('LOWER(name) = ?', ['ayudante'])->first()->id;
        return $this->belongsToMany(Employee::class, 'configgroups')
                    ->where('type_id', $helperId);
    }
    
}
