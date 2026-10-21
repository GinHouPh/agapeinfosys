<?php

namespace Database\Seeders;

use App\Models\DesignTheme;
use Illuminate\Database\Seeder;

class DesignThemeSeeder extends Seeder
{
    /**
     * Seed the theme the site starts with.
     *
     * Its values mirror the `@theme` block in resources/css/app.css, so
     * switching it on changes nothing — it simply makes the tokens
     * editable from the admin.
     */
    public function run(): void
    {
        $tokens = config('design.tokens', []);

        $tokens['font'] = [
            'display' => array_key_first(config('design.fonts.display', [])),
            'body' => array_key_first(config('design.fonts.body', [])),
        ];

        $tokens['radius'] = ['card' => 'none'];
        $tokens['shadow'] = ['card' => 'none'];

        DesignTheme::query()->updateOrCreate(
            ['slug' => 'agape-dark'],
            [
                'name' => 'Agape Dark',
                'description' => 'The default palette the site shipped with.',
                'is_default' => true,
                'is_active' => true,
                'tokens' => $tokens,
            ],
        );
    }
}
