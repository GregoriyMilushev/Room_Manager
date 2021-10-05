<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Room;
use App\Models\Desk;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DeskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_admin_getting_all_desks()
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '12345678',
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $response = $this->get('api/desks');

        $response->assertOk();
        $response->assertStatus(200);
    }

    public function test_admin_create_desk()
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $response = $this->post('api/desks',[
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'small',
            'room_id' => 1,
        ]);

        $response->assertStatus(201);
    }

    public function test_cant_create_different_than_small_or_big_desk()
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $response = $this->post('api/desks',[
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'large',// Large - size
            'room_id' => 1,
        ]);

        $this->assertCount(0, Desk::all());
    }

    public function test_full_room_cant_create_desk()
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '12345678',
            'role' => 'admin'
        ]);
        $this->actingAs($user);

        $room = Room::factory()->create([
            'desk_capacity' => 1
        ]);

        for ($i=0; $i <= 1; $i++) { 
            $response = $this->post('api/desks',[
                'price_per_week' => 15.50,
                'position' => 'centered',
                'size' => 'small',
                'room_id' => 1,
            ]); 
        }

        $response->assertStatus(403);
    }
}
