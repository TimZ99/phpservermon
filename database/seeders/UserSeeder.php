<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder for the User model.
 * 
 * Seeds the database with test users.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates a test admin user and a regular user, each associated with 5 servers.
     */
    public function run(): void
    {
        // Create a test admin user with 5 associated servers
        User::factory()->hasServers(5)
        ->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'admin' => true
        ]);
        
        // Create a regular user with 5 associated servers
        User::factory()->hasServers(5)
        ->create([
            'name' => 'Non-admin',
            'email' => 'regular@example.com'
        ]);
    }
}
