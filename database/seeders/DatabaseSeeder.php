<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            GameModuleSeeder::class,
            GameCategorySeeder::class,
            GameSeeder::class,
        ]);
    }
}
