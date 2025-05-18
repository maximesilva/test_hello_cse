<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected $user;
    protected $token;
    protected $headers;
    
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function actingAsAdmin(): self
    {
        $this->app['auth']->logout();
        $adminRole = Role::create(['name' => 'admin']);
        
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);
        $this->user->assignRole($adminRole);
        
        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);
        
        $this->token = $response->json('token');
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ]);
    }

    protected function actingAsUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $this->app['auth']->logout();
        
        $this->actingAs($user);
        
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ]);
    }
}
