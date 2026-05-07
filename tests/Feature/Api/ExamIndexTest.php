<?php

namespace Tests\Feature\Api;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExamIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_only_sees_active_exams_available_for_application(): void
    {
        $visibleExam = Exam::factory()->create([
            'name' => 'Open Active Exam',
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        Exam::factory()->create([
            'name' => 'Future Active Exam',
            'status' => 'active',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
        ]);

        Exam::factory()->create([
            'name' => 'Expired Active Exam',
            'status' => 'active',
            'start_date' => now()->subDays(4),
            'end_date' => now()->subDay(),
        ]);

        Exam::factory()->create([
            'name' => 'Draft Exam',
            'status' => 'draft',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/exams');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Open Active Exam')
            ->assertJsonPath('data.0.status', 'active');

        $this->assertEquals($visibleExam->ulid, data_get($response->json(), 'data.0.id'));
    }

    public function test_non_privileged_authenticated_user_only_sees_active_open_exams(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Exam::factory()->create([
            'name' => 'Public Active Exam',
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        Exam::factory()->create([
            'name' => 'Moderator Only Draft',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/exams');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Public Active Exam');
    }

    public function test_admin_can_view_all_exam_statuses(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        Exam::factory()->create([
            'name' => 'Admin Draft Exam',
            'status' => 'draft',
        ]);

        Exam::factory()->create([
            'name' => 'Admin Active Exam',
            'status' => 'active',
        ]);

        Exam::factory()->create([
            'name' => 'Admin Closed Exam',
            'status' => 'closed',
        ]);

        $response = $this->getJson('/api/exams?sort=name');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_guest_cannot_bypass_visibility_with_status_filter(): void
    {
        Exam::factory()->create([
            'name' => 'Draft Hidden Exam',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/exams?filter[status]=draft');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}

