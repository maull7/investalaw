<?php

namespace Database\Factories;

use App\Models\CategoryFile;
use App\Models\RegulationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CategoryFile> */
class CategoryFileFactory extends Factory
{
    protected $model = CategoryFile::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'category_id' => RegulationCategory::factory(),
            'filename' => fake()->sentence(3).'.pdf',
            'file_path' => 'category-files/'.fake()->uuid().'.pdf',
        ];
    }
}
