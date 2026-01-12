<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Classroom;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => $this->faker->unique()->numerify('######'),
            'name' => $this->faker->name(),
            'father_name' => $this->faker->name('male'),
            'mother_name' => $this->faker->name('female'),
            'address' => $this->faker->address(),
            'mobile' => '017' . $this->faker->numerify('########'),
            'alt_mobile' => '018' . $this->faker->numerify('########'),
            'class_id' => Classroom::factory(),
            'section' => $this->faker->randomElement(['A', 'B']),
            'nid_file_path' => null,
            'dob' => $this->faker->date('Y-m-d', '-5 years'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'blood_group' => $this->faker->randomElement(['A+', 'B+', 'O+', 'AB+']),
            'last_school' => $this->faker->company() . ' School',
            'siblings_count' => $this->faker->numberBetween(0, 5),
            'birth_order' => $this->faker->numberBetween(1, 5),
            'present_district' => $this->faker->city(),
            'permanent_address' => $this->faker->address(),
            'permanent_district' => $this->faker->city(),
            'guardian_occupation' => $this->faker->jobTitle(),
            'guardian_nationality' => 'Bangladeshi',
            'guardian_phone' => '019' . $this->faker->numerify('########'),
            'guardian_email' => $this->faker->safeEmail(),
            'guardian_nid' => $this->faker->numerify('##########'),
            'shift' => 'Morning',
            'program_type' => 'Schooling',
        ];
    }
}
