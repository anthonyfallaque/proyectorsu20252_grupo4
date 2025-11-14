<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Change.php

public function scheduling() {
    return $this->belongsTo(Scheduling::class);
}

public function oldEmployee() {
    return $this->belongsTo(Employee::class, 'old_employee_id');
}

public function newEmployee() {
    return $this->belongsTo(Employee::class, 'new_employee_id');
}

public function oldVehicle() {
    return $this->belongsTo(Vehicle::class, 'old_vehicle_id');
}

public function newVehicle() {
    return $this->belongsTo(Vehicle::class, 'new_vehicle_id');
}

public function oldShift() {
    return $this->belongsTo(Shift::class, 'old_shift_id');
}

public function newShift() {
    return $this->belongsTo(Shift::class, 'new_shift_id');
}

public function reason() {
    return $this->belongsTo(Reason::class);
}

}
