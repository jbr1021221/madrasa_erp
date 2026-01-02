<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'sections' => ['A', 'B'],
            'max_students_per_section' => 40,
            'fees' => [
                ['name' => 'Monthly Fee', 'amount' => 500, 'type' => 'Monthly']
            ],
            'total_fee' => 500,
        ];
    }
}
