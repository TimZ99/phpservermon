<?php

namespace Tests;

use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $seeder = SettingsSeeder::class;
}
