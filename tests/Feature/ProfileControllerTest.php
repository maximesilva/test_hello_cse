<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Enums\ProfilStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function it_can_list_profiles()
    {
        Profile::factory()->count(3)->create([
            'status' => ProfilStatus::ACTIVE
        ]);

        $response = $this->getJson('/api/profiles');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'first_name',
                            'image_path',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function it_can_create_profile()
    {
        $profileData = [
            'name' => 'Doe',
            'first_name' => 'John',
            'status' => ProfilStatus::ACTIVE->value
        ];

        $response = $this->postJson('/api/profiles', $profileData, $this->headers);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'profile' => [
                        'id',
                        'name',
                        'first_name',
                        'status',
                        'created_by'
                    ]
                ]);

        $this->assertDatabaseHas('profiles', [
            'name' => 'Doe',
            'first_name' => 'John',
            'status' => ProfilStatus::ACTIVE->value
        ]);
    }

    public function it_can_update_profile()
    {
        $profile = Profile::factory()->create();

        $updateData = [
            'name' => 'Updated',
            'first_name' => 'Name',
            'status' => ProfilStatus::INACTIVE->value
        ];

        $response = $this->putJson("/api/profiles/{$profile->id}", $updateData, $this->headers);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'profile' => [
                        'id',
                        'name',
                        'first_name',
                        'status'
                    ]
                ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'name' => 'Updated',
            'first_name' => 'Name',
            'status' => ProfilStatus::INACTIVE->value
        ]);
    }

    public function it_can_delete_profile()
    {
        $profile = Profile::factory()->create();

        $response = $this->deleteJson("/api/profiles/{$profile->id}", [], $this->headers);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message'
                ]);

        $this->assertSoftDeleted('profiles', [
            'id' => $profile->id
        ]);
    }

    public function it_validates_required_fields_when_creating_profile()
    {
        $response = $this->postJson('/api/profiles', [], $this->headers);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'first_name', 'status']);
    }

    public function it_validates_required_fields_when_updating_profile()
    {
        $profile = Profile::factory()->create();

        $response = $this->putJson("/api/profiles/{$profile->id}", [], $this->headers);

        $response->assertStatus(422);
    }

    public function it_returns_404_when_profile_not_found()
    {
        $response = $this->getJson('/api/profiles/non-existent-uuid', $this->headers);

        $response->assertStatus(404);
    }

    public function it_requires_authentication()
    {
        $response = $this->postJson('/api/profiles', []);

        $response->assertStatus(401);
    }

    public function it_requires_admin_role()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        $response = $this->postJson('/api/profiles', [], $headers);

        $response->assertStatus(403);
    }

    #[DataProvider('validationProvider')]
    public function test_validation_in_each_fields_when_creating_profile($field, $value)
    {
        $data = [
            'name' => 'Valid Name',
            'first_name' => 'Valid First Name',
            'status' => ProfilStatus::ACTIVE->value
        ];

        $data[$field] = $value;

        $response = $this->postJson("/api/profiles", $data, $this->headers);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
    }

    #[DataProvider('validationProvider')]
    public function test_validation_in_each_fields_when_updating_profile($field, $value)
    {
        $profile = Profile::factory()->create();

        $data = [
            'name' => 'Valid Name',
            'first_name' => 'Valid First Name',
            'status' => ProfilStatus::ACTIVE->value
        ];

        $data[$field] = $value;

        $response = $this->putJson("/api/profiles/{$profile->id}", $data, $this->headers);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
    }

    public static function validationProvider()
    {
        return [
            'name is required' => [
                'field' => 'name',
                'value' => '',
            ],
            'name is too long' => [
                'field' => 'name',
                'value' => str_repeat('a', 256),
            ],
            'first_name is required' => [
                'field' => 'first_name',
                'value' => '',
            ],
            'first_name is too long' => [
                'field' => 'first_name',
                'value' => str_repeat('a', 256),
            ],
            'status is invalid' => [
                'field' => 'status',
                'value' => 'invalid_status',
            ],
            'status is required' => [
                'field' => 'status',
                'value' => '',
            ],
            'name is not a string' => [
                'field' => 'name',
                'value' => 123,
            ],
            'first_name is not a string' => [
                'field' => 'first_name',
                'value' => 123,
            ],
        ];
    }
}