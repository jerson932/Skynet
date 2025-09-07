<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Inserta roles
       DB::table('roles')->upsert([
    ['name' => 'Administrador', 'slug' => 'admin',      'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Supervisor',    'slug' => 'supervisor', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Técnico',       'slug' => 'tecnico',    'created_at' => now(), 'updated_at' => now()],
], ['slug'], ['name','updated_at']);

        // Obtén id del rol admin
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');

        // Crea/actualiza usuario admin
        User::updateOrCreate(
            ['email' => 'admin@skynet.test'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('Admin123!'),
                'role_id'  => $adminId,
            ]
        );


                    $supervisorId = DB::table('roles')->where('slug','supervisor')->value('id');
            $tecnicoId    = DB::table('roles')->where('slug','tecnico')->value('id');

            $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@skynet.test'],
            ['name' => 'Supervisor', 'password' => Hash::make('Supervisor123!'), 'role_id' => $supervisorId]
            );

            User::updateOrCreate(
            ['email' => 'tecnico@skynet.test'],
            ['name' => 'Tecnico', 'password' => Hash::make('Tecnico123!'), 'role_id' => $tecnicoId, 'supervisor_id' => $supervisor->id ?? null]
            );
    }
}
