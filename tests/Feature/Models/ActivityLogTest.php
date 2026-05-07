<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_model_logs_create_update_and_delete_events(): void
    {
        $exam = Exam::factory()->create([
            'name' => 'Activity Log Exam',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Exam::class,
            'subject_id' => $exam->id,
            'event' => 'created',
            'log_name' => 'exam',
        ]);

        $exam->update(['status' => 'active']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Exam::class,
            'subject_id' => $exam->id,
            'event' => 'updated',
            'log_name' => 'exam',
        ]);

        $exam->delete();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Exam::class,
            'subject_id' => $exam->id,
            'event' => 'deleted',
            'log_name' => 'exam',
        ]);
    }

    public function test_category_model_logs_create_update_and_delete_events(): void
    {
        $category = Category::factory()->create([
            'name' => 'Base Category',
            'type' => 'exam',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Category::class,
            'subject_id' => $category->id,
            'event' => 'created',
            'log_name' => 'category',
        ]);

        $category->update(['name' => 'Updated Category']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Category::class,
            'subject_id' => $category->id,
            'event' => 'updated',
            'log_name' => 'category',
        ]);

        $category->delete();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Category::class,
            'subject_id' => $category->id,
            'event' => 'deleted',
            'log_name' => 'category',
        ]);
    }
}

