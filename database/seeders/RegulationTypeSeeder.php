<?php

namespace Database\Seeders;

use App\Models\RegulationType;
use Illuminate\Database\Seeder;

class RegulationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Undang-Undang', 'level' => 1],
            ['name' => 'Peraturan Pemerintah', 'level' => 2],
            ['name' => 'Peraturan Presiden', 'level' => 2],
            ['name' => 'Peraturan OJK', 'level' => 3],
            ['name' => 'Peraturan Menteri', 'level' => 3],
            ['name' => 'Surat Edaran OJK', 'level' => 4],
            ['name' => 'Keputusan Menteri', 'level' => 4],
            ['name' => 'Peraturan Bank Indonesia', 'level' => 3],
            ['name' => 'Surat Edaran Bank Indonesia', 'level' => 4],
            ['name' => 'Pedoman', 'level' => 5],
            ['name' => 'Surat Edaran', 'level' => 5],
        ];

        foreach ($types as $type) {
            RegulationType::updateOrCreate(
                ['name' => $type['name']],
                ['level' => $type['level']],
            );
        }
    }
}
