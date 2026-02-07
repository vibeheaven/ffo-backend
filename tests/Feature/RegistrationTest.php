<?php

namespace Tests\Feature;

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    public function test_user_can_register_with_minimal_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'authorization' => ['token', 'type', 'expires_in'],
                ],
                'message',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'registration_step' => 5,
        ]);
    }

    public function test_user_can_register_with_full_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Full User',
            'email' => 'full@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'male',
            'birthday' => '1990-01-01',
            'location' => 'New York',
            'language' => 'tr',
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('users', [
            'email' => 'full@example.com',
            'gender' => 'male',
            'birthday' => '1990-01-01',
            'location' => 'New York',
            'language' => 'tr',
        ]);
    }

    public function test_registration_validation_fails()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            // Missing email
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_duplicate_email_fails()
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
