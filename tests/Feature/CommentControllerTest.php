<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_create_comment()
    {
        $profile = Profile::factory()->create();
        $commentData = [
            'content' => $this->faker->paragraph
        ];

        $response = $this->actingAsAdmin()->postJson("/api/profiles/{$profile->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'comment' => [
                    'id',
                    'content',
                    'profile_id',
                    'created_by',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'message' => 'Commentaire ajouté avec succès',
                'comment' => [
                    'content' => $commentData['content'],
                    'profile_id' => $profile->id,
                    'created_by' => $this->user->id
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => $commentData['content'],
            'profile_id' => $profile->id,
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_cannot_create_comment_on_nonexistent_profile()
    {
        $commentData = [
            'content' => $this->faker->paragraph
        ];

        $response = $this->actingAsAdmin()->postJson("/api/profiles/non-existent-uuid/comments", $commentData);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_cannot_create_duplicate_comment()
    {
        $profile = Profile::factory()->create();
        $commentData = [
            'content' => $this->faker->paragraph
        ];

        $this->actingAsAdmin()->postJson("/api/profiles/{$profile->id}/comments", $commentData);

        $response = $this->postJson("/api/profiles/{$profile->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $profile = Profile::factory()->create();
        $commentData = [
            'content' => $this->faker->paragraph
        ];

        $response = $this->postJson("/api/profiles/{$profile->id}/comments", $commentData);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_admin_role_to_create_comment()
    {
        $profile = Profile::factory()->create();

        $commentData = [
            'content' => $this->faker->paragraph
        ];

        $response = $this->actingAsUser()->postJson("/api/profiles/{$profile->id}/comments", $commentData);

        $response->assertStatus(403);
    }

    #[DataProvider('validationProvider')]
    public function test_validation_in_each_fields_when_creating_comment($field, $value)
    {
        $profile = Profile::factory()->create();
        $data = [
            'content' => $this->faker->paragraph
        ];

        $data[$field] = $value;

        $response = $this->actingAsAdmin()->postJson("/api/profiles/{$profile->id}/comments", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    public static function validationProvider()
    {
        return [
            'content is required' => [
                'field' => 'content',
                'value' => '',
            ],
            'content is too short' => [
                'field' => 'content',
                'value' => 'ab',
            ],
            'content is too long' => [
                'field' => 'content',
                'value' => str_repeat('a', 1001),
            ],
            'content is not a string' => [
                'field' => 'content',
                'value' => 123,
            ],
        ];
    }
}