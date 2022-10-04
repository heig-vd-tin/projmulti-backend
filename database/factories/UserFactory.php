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
            'initials' => $name[0][0] . $name[1][0],
            'email' => $this->faker->unique()->safeEmail(),
            'role' => UserRole::STUDENT,
            'orientation_id' => rand(1, 3)
        ];
    }
}
