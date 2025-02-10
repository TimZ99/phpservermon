<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->hasServers(5)
        ->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'admin' => true
        ]);
        User::factory()->hasServers(5)
        ->create([
            'name' => 'Non-admin',
            'email' => 'regular@example.com'
        ]);
    }
}
