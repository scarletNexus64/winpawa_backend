<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name' => 'Français',
                'code' => 'fr',
                'locale' => 'fr_FR',
                'flag_emoji' => '🇫🇷',
                'is_active' => true,
                'is_default' => true,
                'is_rtl' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'English',
                'code' => 'en',
                'locale' => 'en_US',
                'flag_emoji' => '🇬🇧',
                'is_active' => true,
                'is_default' => false,
                'is_rtl' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
