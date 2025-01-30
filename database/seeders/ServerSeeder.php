<?php

namespace Database\Seeders;

use App\Models\Server;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            'port' => random_int(0,9999),
        ]);
    }
}
