<?php

namespace Tests\Feature\Admin;

use App\Models\Declarant;
use App\Models\Department;
use App\Models\Entity;
use App\Models\Grievance;
use App\Models\Role;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrievanceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_a_grievance_with_audit_history(): void
    {
        $admin = $this->makeAdmin();
        $sector = Sector::create(['name' => 'Voirie']);
        $department = Department::create(['name' => 'Services techniques']);

        $created = $this->actingAs($admin)->postJson('/api/grievances', [
            'title' => 'Nid-de-poule dans la rue principale',
            'sector_id' => $sector->id,
            'department_id' => $department->id,
            'description' => 'La chaussée est dégradée.',
        ]);

        $created->assertCreated()->assertJsonPath('data.status', 'submitted');
        $grievanceId = $created->json('data.id');
        $this->assertDatabaseHas('grievances', ['id' => $grievanceId, 'status' => 'submitted']);
        $this->assertDatabaseHas('grievance_status_histories', ['grievance_id' => $grievanceId, 'status' => 'submitted', 'changed_by_id' => $admin->id]);
        $this->assertDatabaseHas('grievance_assignment_histories', ['grievance_id' => $grievanceId, 'department_id' => $department->id, 'changed_by_id' => $admin->id]);

        $this->actingAs($admin)->putJson("/api/grievances/{$grievanceId}", [
            'title' => 'Nid-de-poule traité',
            'sector_id' => $sector->id,
            'department_id' => null,
            'description' => 'La chaussée a été réparée.',
            'status' => 'processed',
            'complained_at' => now()->subDay()->toDateTimeString(),
        ])->assertOk()->assertJsonPath('data.status', 'processed');

        $this->assertDatabaseHas('grievance_status_histories', ['grievance_id' => $grievanceId, 'status' => 'processed', 'changed_by_id' => $admin->id]);
        $this->assertDatabaseHas('grievance_assignment_histories', ['grievance_id' => $grievanceId, 'department_id' => null, 'changed_by_id' => $admin->id]);
        $updated = Grievance::findOrFail($grievanceId);
        $this->assertNotNull($updated->due_at);
        $this->assertEquals(48 * 60, $updated->complained_at->diffInMinutes($updated->due_at));
    }

    public function test_due_date_must_be_after_the_complaint_date(): void
    {
        $admin = $this->makeAdmin();
        $sector = Sector::create(['name' => 'Environnement']);
        $date = now()->subHour()->toDateTimeString();

        $this->actingAs($admin)->postJson('/api/grievances', [
            'title' => 'Date invalide', 'sector_id' => $sector->id, 'description' => 'Test',
            'complained_at' => $date, 'due_at' => $date,
        ])->assertUnprocessable()->assertJsonValidationErrors('due_at');
    }

    public function test_a_regular_authenticated_user_can_manage_reference_data_and_grievances(): void
    {
        $user = User::factory()->create();

        $department = $this->actingAs($user)->postJson('/api/departments', ['name' => 'Accueil'])->assertCreated()->json('data');
        $sector = $this->actingAs($user)->postJson('/api/sectors', ['name' => 'Propreté'])->assertCreated()->json('data');

        $this->actingAs($user)->postJson('/api/grievances', [
            'title' => 'Poubelle pleine', 'sector_id' => $sector['id'], 'department_id' => $department['id'], 'description' => 'Demande citoyenne.',
        ])->assertCreated();
    }

    public function test_authenticated_users_can_manage_entities_and_delete_only_unused_entities(): void
    {
        $user = User::factory()->create();

        $entity = $this->actingAs($user)->postJson('/api/entities', [
            'title' => 'Centre-ville',
            'address' => 'Rue principale',
            'latitude' => -18.8792,
            'longitude' => 47.5079,
            'description' => 'Zone urbaine.',
        ])->assertCreated()->assertJsonPath('data.title', 'Centre-ville')->json('data');

        $this->actingAs($user)->putJson("/api/entities/{$entity['id']}", [
            'title' => 'Centre municipal',
            'address' => 'Avenue du marché',
            'latitude' => -18.87,
            'longitude' => 47.5,
            'description' => 'Zone actualisée.',
        ])->assertOk()->assertJsonPath('data.title', 'Centre municipal');

        $sector = Sector::create(['name' => 'Voirie']);
        $grievance = $this->actingAs($user)->postJson('/api/grievances', [
            'title' => 'Éclairage en panne',
            'sector_id' => $sector->id,
            'entity_id' => $entity['id'],
            'description' => 'Lampadaire hors service.',
        ])->assertCreated()->json('data');

        $this->assertSame($entity['id'], $grievance['entity']['id']);
        $this->actingAs($user)->deleteJson("/api/entities/{$entity['id']}")
            ->assertStatus(409);

        $unused = Entity::create(['title' => 'Entité inutilisée']);
        $this->actingAs($user)->deleteJson("/api/entities/{$unused->id}")->assertNoContent();
    }

    public function test_grievances_can_create_reuse_and_filter_multiple_declarants(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'Propreté']);
        $entity = Entity::create(['title' => 'Marché central']);

        $created = $this->actingAs($user)->postJson('/api/grievances', [
            'title' => 'Collecte des déchets',
            'sector_id' => $sector->id,
            'entity_id' => $entity->id,
            'description' => 'Les déchets n’ont pas été collectés.',
            'declarants' => [[
                'first_name' => 'Miora',
                'last_name' => 'Raja',
                'email' => 'miora@example.com',
                'address' => 'Analakely',
                'phone' => '0340000000',
            ]],
        ])->assertCreated()
            ->assertJsonPath('data.entity.title', 'Marché central')
            ->assertJsonPath('data.declarants.0.name', 'Miora Raja')
            ->json('data');

        $declarant = Declarant::query()->where('email', 'miora@example.com')->firstOrFail();
        $this->assertDatabaseHas('declarant_grievance', [
            'declarant_id' => $declarant->id,
            'grievance_id' => $created['id'],
        ]);

        $this->actingAs($user)->getJson('/api/declarants?search=miora')
            ->assertOk()
            ->assertJsonPath('data.0.id', $declarant->id);

        $second = $this->actingAs($user)->postJson('/api/grievances', [
            'title' => 'Demande complémentaire',
            'sector_id' => $sector->id,
            'entity_id' => $entity->id,
            'description' => 'Autre signalement.',
            'declarants' => [['id' => $declarant->id]],
        ])->assertCreated()->json('data');

        $this->assertSame(1, Declarant::query()->count());
        $this->assertDatabaseHas('declarant_grievance', [
            'declarant_id' => $declarant->id,
            'grievance_id' => $second['id'],
        ]);

        $this->actingAs($user)->getJson('/api/grievances/dashboard?sector_id='.$sector->id.'&entity_id='.$entity->id)
            ->assertOk()
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.by_status.submitted', 2);

        $this->actingAs($user)->getJson('/api/grievances?search=miora')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->actingAs($user)->putJson('/api/grievances/'.$second['id'], [
            'title' => 'Demande complémentaire',
            'sector_id' => $sector->id,
            'entity_id' => $entity->id,
            'description' => 'Autre signalement.',
            'status' => 'submitted',
            'complained_at' => now()->toDateTimeString(),
            'declarants_present' => true,
        ])->assertOk()->assertJsonPath('data.declarants', []);

        $this->assertDatabaseMissing('declarant_grievance', [
            'declarant_id' => $declarant->id,
            'grievance_id' => $second['id'],
        ]);
    }

    public function test_new_declarant_names_are_required_but_the_declarant_list_is_optional(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'Environnement']);

        $this->actingAs($user)->postJson('/api/grievances', [
            'title' => 'Signalement sans déclarant',
            'sector_id' => $sector->id,
            'description' => 'Description.',
        ])->assertCreated()->assertJsonPath('data.declarants', []);

        $this->actingAs($user)->postJson('/api/grievances', [
            'title' => 'Signalement avec déclarant invalide',
            'sector_id' => $sector->id,
            'description' => 'Description.',
            'declarants' => [['first_name' => 'Tiana']],
        ])->assertUnprocessable()->assertJsonValidationErrors('declarants.0.last_name');
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $admin->roleAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);
        $admin->roleAssignments()->create(['role_id' => $role->id, 'assigned_at' => now()]);

        return $admin;
    }
}
