<?php

namespace Database\Factories;

use App\Constants\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = explode(' ', $this->faker->name(), 2);
        return [
            'firstname' => $name[0],
            'lastname' => $name[1],
            'email' => $this->faker->unique()->safeEmail(),
            'role' => UserRole::STUDENT,
            'orientation_id' => rand(1, 3)
        ];
    }
}
