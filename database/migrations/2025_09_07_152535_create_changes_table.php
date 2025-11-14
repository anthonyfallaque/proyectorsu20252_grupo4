<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduling_id');
            $table->foreign('scheduling_id')->references('id')->on('schedulings');
            $table->unsignedBigInteger('new_employee_id')->nullable();
            $table->foreign('new_employee_id')->references('id')->on('employees');
            $table->unsignedBigInteger('old_employee_id')->nullable();
            $table->foreign('old_employee_id')->references('id')->on('employees');
            $table->unsignedBigInteger('new_vehicle_id')->nullable();
            $table->foreign('new_vehicle_id')->references('id')->on('vehicles');
            $table->unsignedBigInteger('old_vehicle_id')->nullable();
            $table->foreign('old_vehicle_id')->references('id')->on('vehicles');
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->foreign('reason_id')->references('id')->on('reasons');
            $table->unsignedBigInteger('new_shift_id')->nullable();
            $table->foreign('new_shift_id')->references('id')->on('shifts');
            $table->unsignedBigInteger('old_shift_id')->nullable();
            $table->foreign('old_shift_id')->references('id')->on('shifts');
            $table->date('change_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changes');
    }
};
