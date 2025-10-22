<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // seed roles
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            ['name' => 'Administrador', 'slug' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Técnico', 'slug' => 'tecnico', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_admin_can_create_supervisor_and_tecnico()
    {
    $adminRole = Role::where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/settings/users', [
                'name' => 'Sup One',
                'email' => 'sup1@test',
                'role' => 'supervisor',
                'password' => 'secret123'
            ])->assertStatus(201);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/settings/users', [
                'name' => 'Tec One',
                'email' => 'tec1@test',
                'role' => 'tecnico',
                'password' => 'secret123'
            ])->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'sup1@test']);
        $this->assertDatabaseHas('users', ['email' => 'tec1@test']);
    }

    public function test_supervisor_can_view_their_tecnicos()
    {
    $supervisorRole = Role::where('slug', 'supervisor')->firstOrFail();
    $tecnicoRole = Role::where('slug', 'tecnico')->firstOrFail();

    $supervisor = User::factory()->create(['role_id' => $supervisorRole->id]);
    $tec1 = User::factory()->create(['role_id' => $tecnicoRole->id, 'supervisor_id' => $supervisor->id]);
    $tec2 = User::factory()->create(['role_id' => $tecnicoRole->id, 'supervisor_id' => $supervisor->id]);

        $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/settings/users')
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $tec1->email])
            ->assertJsonFragment(['email' => $tec2->email]);
    }

    public function test_tecnico_cannot_create_user()
    {
    $tecnicoRole = Role::where('slug', 'tecnico')->firstOrFail();
    $tec = User::factory()->create(['role_id' => $tecnicoRole->id]);

        $this->actingAs($tec, 'sanctum')
            ->postJson('/api/settings/users', [
                'name' => 'Bad',
                'email' => 'bad@test',
                'role' => 'tecnico',
            ])->assertStatus(403);
    }
}
