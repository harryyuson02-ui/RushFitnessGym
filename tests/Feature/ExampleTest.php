<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_welcome_page_is_available(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Welcome to Rush')
            ->assertSee('Continue to login')
            ->assertSee(route('login'));
    }
}
