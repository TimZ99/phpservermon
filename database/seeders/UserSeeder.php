<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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
        // Create a powerful test user with 5 associated servers
        $admin = User::factory()->hasServers(5)
            ->create([
                'name' => 'Admin User',
                'email' => 'adminuser@example.com',
            ]);
        $admin->set_scopes($admin->valid_scopes());
        $admin->save();
        // Create a regular user with 5 associated servers
        User::factory()->hasServers(5)
            ->create([
                'name' => 'Regular User',
                'email' => 'regularuser@example.com',
            ]);

        // Create a regular user with 5 associated servers
        User::factory()->hasServers(5)
            ->create([
                'name' => 'Suspended User',
                'email' => 'suspendeduser@example.com',
                'suspended' => true,
            ]);
    }
}
