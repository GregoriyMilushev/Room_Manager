<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Room;
use App\Models\Desk;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->manager = User::factory()->create([
            'role' => 'room manager',
        ]);

        $this->client = User::factory()->create([
            'name' => 'Joro',
        ]);

        $this->room = Room::factory()->create([
            'manager_id' => $this->manager->id
        ]);

        $this->desk = Desk::factory()->create([
            'room_id' => $this->room->id
        ]);
    }

    public function test_client_cant_get_rooms()
    {
        $response = $this->actingAs($this->client)->get('api/rooms');

        $response->assertStatus(403);
        $response->assertSee('Clients are not allowed');
    }

    public function test_manager_can_get_only_his_room()
    {
        $response = $this->actingAs($this->manager)->get('api/rooms');

        $room = $response->getData();

        $response->assertStatus(200);
        $this->assertTrue($this->room->id == $room->data->id);
    }

    public function test_admin_can_get_all_rooms()
    {
        $response = $this->actingAs($this->admin)->get('api/rooms');

        $rooms = $response->getData();

        $response->assertStatus(200);
        $this->assertCount(1,$rooms->data);
    }

    public function test_admin_can_get_every_chosen_room()
    {
        $response = $this->actingAs($this->admin)->get('api/rooms/' . $this->room->id);

        $room = $response->getData();

        $response->assertStatus(200);
        $this->assertTrue($room->data->id == $this->room->id);
    }

    public function test_manager_cant_get_every_chosen_room()
    {
        $response = $this->actingAs($this->manager)->get('api/rooms/' . $this->room->id);

        $response->assertStatus(401);
    }

    public function test_admin_can_store_new_room()
    {
        $response = $this->actingAs($this->admin)->post('api/rooms',[
            'size' => 'small',
        ]);

        $response->assertStatus(201);
        $this->assertCount(2, Room::all());
    }
}
