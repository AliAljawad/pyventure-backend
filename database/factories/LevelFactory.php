<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'category' => $this->faker->randomElement(['Syntax', 'Loops', 'Functions', 'OOP']),
            'question' => $this->faker->text(300),
            'solution' => 'print("Hello World")'
        ];
    }

}
