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
         Schema::create('clients', function (Blueprint $table) {
        $table->id();
        $table->string('name');                 // Nombre del cliente
        $table->string('contact_name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('address')->nullable();
        $table->decimal('lat', 10, 7)->nullable(); // coordenadas
        $table->decimal('lng', 10, 7)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
