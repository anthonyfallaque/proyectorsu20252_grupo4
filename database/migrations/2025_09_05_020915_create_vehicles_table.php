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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 100);
            $table->string('plate', 20);
            $table->integer('year');
            $table->double('load_capacity');
            $table->double('fuel_capacity');
            $table->double('compactation_capacity');
            $table->integer('people_capacity');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('color_id');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('model_id');
            $table->foreign('color_id')->references('id')->on('colors');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('type_id')->references('id')->on('vehicletypes');
            $table->foreign('model_id')->references('id')->on('brandmodels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
