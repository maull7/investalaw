<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use App\Models\TypePrompt;
use Illuminate\Database\Seeder;

class TypePromptSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Analisa', 'slug' => 'analisa', 'description' => 'Analisa perbandingan dokumen'],
            ['name' => 'Review', 'slug' => 'review', 'description' => 'Review kesesuaian dokumen'],
            ['name' => 'Rekomendasi', 'slug' => 'rekomendasi', 'description' => 'Review dan rekomendasi dokumen'],
            ['name' => 'Validitas', 'slug' => 'validitas', 'description' => 'Validitas dokumen'],
        ];

        foreach ($types as $type) {
            TypePrompt::updateOrCreate(['slug' => $type['slug']], $type);
        }

        // Backfill: hubungkan ai_prompts yang sudah ada dengan type_prompt berdasarkan slug lama.
        AiPrompt::whereNull('type_prompt_id')->each(function (AiPrompt $prompt): void {
            $type = TypePrompt::where('slug', $prompt->type)->first();
            if ($type) {
                $prompt->update(['type_prompt_id' => $type->id]);
            }
        });
    }
}
