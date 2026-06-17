<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use Illuminate\Database\Seeder;

class AiPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            [
                'type' => 'analisa',
                'title' => 'Prompt Analisa Dokumen',
                'prompt_text' => 'Buatkan analisa atas dokumen dengan mengacu kepada peraturan-peraturan yang ada di dalam daftar dengan memperhatikan kesesuaian dengan peraturan sehingga memberikan catatan pada setiap subbab mengacu kepada ketentuan apa dan jika tidak ada acuan pada ketentuan yang ada maka diberikan catatan juga sebagai klausul/informasi tambahan.',
            ],
            [
                'type' => 'review',
                'title' => 'Prompt Review Dokumen',
                'prompt_text' => 'Buatkan review atas dokumen dengan mengacu kepada peraturan-peraturan yang ada di dalam daftar dengan memperhatikan kesesuaian dengan peraturan sehingga dapat diinformasikan apakah ada kesesuaian dan ketidak sesuaian dalam dokumen tersebut.',
            ],
            [
                'type' => 'rekomendasi',
                'title' => 'Prompt Rekomendasi Dokumen',
                'prompt_text' => 'Buatkan review atas dokumen dengan mengacu kepada peraturan-peraturan yang ada di dalam daftar dengan memperhatikan kesesuaian dengan peraturan sehingga dapat diinformasikan apakah ada kesesuaian dan ketidak sesuaian dalam dokumen tersebut serta rekomendasi penambahan klausul untuk dapat memenuhi ketentuan tersebut.',
            ],
            [
                'type' => 'validitas',
                'title' => 'Prompt Validitas Dokumen',
                'prompt_text' => '',
            ],
        ];

        foreach ($prompts as $prompt) {
            AiPrompt::updateOrCreate(
                ['type' => $prompt['type']],
                $prompt
            );
        }
    }
}
