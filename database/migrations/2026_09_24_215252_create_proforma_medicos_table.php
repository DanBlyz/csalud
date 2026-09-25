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
        Schema::create('proforma_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained('proformas')->cascadeOnDelete();
            $table->foreignId('medico_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['proforma_id', 'medico_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_medicos');
    }
};
