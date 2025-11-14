<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    // Protegemos los campos que no se quieren asignar en masa
    // o si quieres permitir todo excepto el id, usa guarded vacío
    protected $guarded = [];

    // Relaciones
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function type()
    {
        return $this->belongsTo(Vehicletype::class);
    }

    public function model()
    {
        return $this->belongsTo(Brandmodel::class);
    }
    public function employeegroups()
    {
        return $this->hasMany(Employeegroup::class);
    }
    
}
