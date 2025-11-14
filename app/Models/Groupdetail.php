<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupdetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function group()
    {
        return $this->belongsTo(Employeegroup::class);
    }

    public function scheduling()
    {
        return $this->belongsTo(Scheduling::class, 'scheduling_id'); // Asumiendo que la columna foránea es 'scheduling_id'
    }

}
