<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            UserSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                DemoDataSeeder::class,
            ]);
        }
    }
}
