<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->date('activity_date');
            $table->text('observation')->nullable();  // CAMBIO: description → observation y nullable
            $table->string('image', 255)->nullable();
            $table->tinyInteger('completed')->default(0);  // NUEVO: estado realizado/no realizado
            $table->integer('status')->default(1);
            $table->foreign('schedule_id')->references('id')->on('maintenance_schedules')->onDelete('cascade');  // CAMBIO: cascade en vez de restrict
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_activities');
    }
};
