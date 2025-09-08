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
        Schema::create('visits', function (Blueprint $table) {
        $table->id();

        $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
        $table->foreignId('supervisor_id')->constrained('users'); // quien planifica
        $table->foreignId('tecnico_id')->constrained('users');    // quien ejecuta

        $table->timestamp('scheduled_at'); // fecha/hora programada

        // check-in
        $table->timestamp('check_in_at')->nullable();
        $table->decimal('check_in_lat', 10, 7)->nullable();
        $table->decimal('check_in_lng', 10, 7)->nullable();

        // check-out
        $table->timestamp('check_out_at')->nullable();
        $table->decimal('check_out_lat', 10, 7)->nullable();
        $table->decimal('check_out_lng', 10, 7)->nullable();

        $table->text('notes')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
