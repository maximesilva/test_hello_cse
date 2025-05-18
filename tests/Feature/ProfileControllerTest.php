<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Enums\ProfilStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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

    /** @test */
    public function it_can_create_profile()
    {
        $profileData = [
            'name' => 'Doe',
            'first_name' => 'John',
            'image' => UploadedFile::fake()->image('test.jpg'),
            'status' => ProfilStatus::ACTIVE->value
        ];

        $response = $this->actingAsAdmin()->postJson('/api/profiles', $profileData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'profile' => [
                        'id',
                        'name',
                        'first_name',
                        'image_path',
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

    /** @test */
    public function it_can_update_profile()
    {
        $profile = Profile::factory()->create();

        $updateData = [
            'name' => 'Updated',
            'first_name' => 'Name',
            'status' => ProfilStatus::INACTIVE->value
        ];

        $response = $this->actingAsAdmin()->putJson("/api/profiles/{$profile->id}", $updateData);

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

    /** @test */
    public function it_can_delete_profile()
    {
        $profile = Profile::factory()->create();

        $response = $this->actingAsAdmin()->deleteJson("/api/profiles/{$profile->id}", []);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message'
                ]);

        $this->assertSoftDeleted('profiles', [
            'id' => $profile->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_profile()
    {
        $response = $this->actingAsAdmin()->postJson('/api/profiles', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'first_name', 'status']);
    }

    /** @test */
    public function it_validates_required_fields_when_updating_profile()
    {
        $profile = Profile::factory()->create();

        $response = $this->actingAsAdmin()->putJson("/api/profiles/{$profile->id}", [
            'name' => '',
            'first_name' => '',
            'image' => '',
            'status' => ''
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_requires_authentication_to_create_profile()
    {
        $this->app['auth']->logout();

        $response = $this->postJson('/api/profiles', []);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_admin_role_to_create_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        $response = $this->actingAsUser()->postJson('/api/profiles', [], $headers);

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

        $response = $this->actingAsAdmin()->postJson("/api/profiles", $data);

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

        $response = $this->actingAsAdmin()->putJson("/api/profiles/{$profile->id}", $data);

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