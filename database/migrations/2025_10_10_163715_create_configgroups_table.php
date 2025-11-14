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
        Schema::create('configgroups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employeegroup_id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employeegroup_id')->references('id')->on('employeegroups');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configgroups');
    }
};
