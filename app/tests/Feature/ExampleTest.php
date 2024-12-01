<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    #[Test]
    public function route_should_return_in_order(): void
    {
        $response = $this->get('/');
        $response->assertSeeInOrder(['Documentation', 'Laracast', 'Laravel News', 'Vibrant Ecosystem']);
    }

    #[Test]
    public function about_route_should_return_something(): void
    {
        $response = $this->get('/about');
        $response->assertSee('About');
    }
}
