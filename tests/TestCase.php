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
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ];
    }
}
