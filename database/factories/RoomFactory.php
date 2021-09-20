<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Room::class;

    
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $size = $this->faker->randomElement(['small', 'big']);
        $desk_capacity;

        if ($size == 'big') {
            $desk_capacity = $this->faker->numberBetween(10,15);
        }
        else if($size == 'small'){
            $desk_capacity = $this->faker->numberBetween(5,10);
        }

        return [
            'desk_capacity' => $desk_capacity,
            'size' => $size,
            'created_at' => now(),
        ];
    }
}
