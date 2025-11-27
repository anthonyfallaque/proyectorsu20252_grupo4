<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('responsible_id');
            $table->enum('maintenance_type', ['PREVENTIVO', 'LIMPIEZA', 'REPARACION']);
            $table->enum('day_of_week', ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('status')->default(1);
            $table->foreign('maintenance_id')->references('id')->on('maintenances')->onDelete('restrict');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('restrict');
            $table->foreign('responsible_id')->references('id')->on('employees')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
