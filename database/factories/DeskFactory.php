<?php

namespace Database\Factories;

use App\Models\Desk;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

class DeskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Desk::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $room = Room::inRandomOrder()->first();

        //$room_id;

        // foreach ($rooms as $room) {
        //     $desk_capacity = Room::where('id', $room->id)->first()->desk_capacity;
        //     $desks_count = Desk::where('room_id', $room->id)->count();

        //     if ($desk_capacity >= $desks_count) {
        //         $room_id = $room->id;
        //         break;
        //     }
        // }

        $size = $this->faker->randomElement(['small', 'big']);
        $price;

        if ($size == 'small') {
           $price = $this->faker->numberBetween($min = 25.50, $max = 45.50);
        }
        else if($size == 'big') {
            $price = $this->faker->numberBetween($min = 39.50, $max = 55.50);
        }

        return [
            'price_per_week' => $price,
            'size' => $size,
            'position' => $this->faker->randomElement(['next to Window', 'centerd', 'next to Door', 'next to the Wall']),
            'room_id' => $room->id,
        ];
    }
}
