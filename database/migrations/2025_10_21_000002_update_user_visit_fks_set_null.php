<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration targets Postgres. It drops existing FK constraints on the mentioned columns
        // and re-creates them with ON DELETE SET NULL using raw SQL. This avoids the need for doctrine/dbal.

        // Drop existing constraints if they exist, then add them with ON DELETE SET NULL
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_supervisor_id_foreign');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_supervisor_id_foreign FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL');

        DB::statement('ALTER TABLE visits DROP CONSTRAINT IF EXISTS visits_tecnico_id_foreign');
        DB::statement('ALTER TABLE visits DROP CONSTRAINT IF EXISTS visits_supervisor_id_foreign');
        DB::statement('ALTER TABLE visits ADD CONSTRAINT visits_tecnico_id_foreign FOREIGN KEY (tecnico_id) REFERENCES users(id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE visits ADD CONSTRAINT visits_supervisor_id_foreign FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: drop our SET NULL constraints and recreate default ones (no ON DELETE clause)
        DB::statement('ALTER TABLE visits DROP CONSTRAINT IF EXISTS visits_tecnico_id_foreign');
        DB::statement('ALTER TABLE visits DROP CONSTRAINT IF EXISTS visits_supervisor_id_foreign');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_supervisor_id_foreign');

        DB::statement('ALTER TABLE visits ADD CONSTRAINT visits_tecnico_id_foreign FOREIGN KEY (tecnico_id) REFERENCES users(id)');
        DB::statement('ALTER TABLE visits ADD CONSTRAINT visits_supervisor_id_foreign FOREIGN KEY (supervisor_id) REFERENCES users(id)');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_supervisor_id_foreign FOREIGN KEY (supervisor_id) REFERENCES users(id)');
    }
};
