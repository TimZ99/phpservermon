<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Server::factory(10)->create([
            'name' => Str::random(10),
            'ip' => Str::random(15),
            'port' => Int::random(4),
        ]);
    }
}
