<?php

namespace Tests\Feature;

use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_does_not_show_payment_amount_banner(): void
    {
        config(['sslcommerz.default_amount' => 1250]);

        $exam = Exam::factory()->create([
            'name' => 'Upcoming BIGM Admission',
            'status' => 'active',
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(7),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText($exam->name);
        $response->assertSeeText('Applications Not Yet Open');
        $response->assertDontSeeText('Payment Information: Application Fee BDT 1,250.00');
    }
}

