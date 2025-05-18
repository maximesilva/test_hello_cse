<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ])
            ->assertJson([
                'user' => [
                    'email' => 'test@example.com'
                ]
            ]);
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[DataProvider('loginValidationProvider')]
    public function test_validation_in_each_fields_when_logging_in($field, $value)
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $data[$field] = $value;

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    public function it_can_logout_authenticated_user()
    {
        $response = $this->postJson('/api/logout', [], $this->headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Déconnexion réussie']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'tokenable_type' => User::class
        ]);
    }

    /** @test */
    public function it_cannot_logout_unauthenticated_user()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_get_authenticated_user_info()
    {
        $response = $this->actingAsAdmin()->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ])
            ->assertJson([
                'user' => [
                    'email' => 'admin@example.com'
                ]
            ]);
    }

    /** @test */
    public function it_cannot_get_user_info_when_unauthenticated()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public static function loginValidationProvider()
    {
        return [
            'email is required' => [
                'field' => 'email',
                'value' => '',
            ],
            'email must be valid' => [
                'field' => 'email',
                'value' => 'not-an-email',
            ],
            'email must exist' => [
                'field' => 'email',
                'value' => 'nonexistent@example.com',
            ],
            'password is required' => [
                'field' => 'password',
                'value' => '',
            ],
            'password must be at least 8 characters' => [
                'field' => 'password',
                'value' => 'short',
            ],
        ];
    }
}
