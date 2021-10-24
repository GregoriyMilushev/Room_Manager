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

    public function test_new_room_size_can_be_big_or_small()
    {
        $response = $this->actingAs($this->admin)->post('api/rooms',[
            'size' => 'large',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('size');
    }

    public function test_manager_cant_delete_room()
    {
        $response = $this->actingAs($this->manager)->delete('api/rooms/' . $this->room->id);

        $response->assertStatus(401);
    }

    public function test_admin_deletes_room_and_her_desks()
    {
        $response = $this->actingAs($this->admin)->delete('api/rooms/' . $this->room->id);

        $response->assertStatus(200);
        $this->assertCount(0, Room::all());
        $this->assertCount(0, Desk::all());
    }

    public function test_deleted_room_makes_manager_role_to_client()
    {
        $response = $this->actingAs($this->admin)->delete('api/rooms/' . $this->room->id);

        $old_manager = User::find($this->manager->id);

        $response->assertStatus(200);
        $this->assertTrue($old_manager->role == 'client'); 
    }

    public function test_deleted_room_with_admin_manager_dont_change_role_to_client()
    {
        $room2 = Room::factory()->create([
            'manager_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete('api/rooms/' . $room2->id);

        $old_manager = json_decode($response->content())->user->attributes;

        $response->assertStatus(200);
        $this->assertTrue($old_manager->role == 'admin'); 
    }

    public function test_admin_update_room_manager()
    {
        $response = $this->actingAs($this->admin)->patch('api/rooms/' . $this->room->id,[
            'manager_id' => $this->client->id,
        ]);

        $manager = json_decode($response->content())->data->attributes->manager;

        $response->assertStatus(200);
        $this->assertTrue($manager->id == $this->client->id); 
        $this->assertTrue($manager->role == 'room manager'); 
    }
}
