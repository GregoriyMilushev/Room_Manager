<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Room;
use App\Models\Desk;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class DeskTest extends TestCase
{
    use DatabaseMigrations;

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
        // $response->assertStatus(200);
    }

    public function test_room_manager_can_only_sees_his_room_desks()
    {
        $manager = User::factory()->create([
            'role' => 'room manager',
        ]);

        $this->actingAs($manager);

        $room = Room::factory()->create([
            'manager_id' => 1,
        ]);

        $desks = Desk::factory(3)->create([
            'room_id' => 1,
        ]);

        $response = $this->getJson('api/desks');
        $response->assertStatus(200);
        //$response->assertJson(['room_id' => '1']);
    }

    public function test_client_sees_his_rented_desk()
    {
        $manager = User::factory()->create([
            'role' => 'room manager'
        ]);

        $client = User::factory()->create();

        $this->actingAs($client);

        $room = Room::factory()->create();

        $desks = Desk::factory()->create([
            'is_taken' => true,
            'user_id' => 2,
        ]);

        $response = $this->get('api/desks');
        $response->assertStatus(200);
        //$response->assertJson(["room_id" => "1"]);
    }    
    
    public function test_clien_can_only_sees_his_rented_desk()
    {
        $manager = User::factory()->create([
            'role' => 'room manager'
        ]);

        $client = User::factory()->create();

        $this->actingAs($client);

        $room = Room::factory()->create();

        $desks = Desk::factory()->create();

        $response = $this->get('api/desks');
        $response->assertStatus(403);
        //$response->assertJson(["room_id" => "1"]);
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

    public function test_client_cant_store_desk()
    {
        $client = User::factory()->create();

        $this->actingAs($client);

        $room = Room::factory()->create();

        $response = $this->post('api/desks',[
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'small',
            'room_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_cant_create_different_than_small_or_big_desk_size()
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

    public function test_full_room_cant_store_desk()
    {
        $user = User::factory()->create([
            'role' => 'admin',
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

    public function test_admin_delete_desk()
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $this->post('api/desks',[
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'small',
            'room_id' => 1,
        ]);

        $response = $this->delete('api/desks/1');

        $response->assertStatus(200);
    }

    public function test_admin_updating_desk()
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $this->post('api/desks',[
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'small',
            'room_id' => 1,
        ]);

        $response = $this->put('api/desks/1',[
            'position' => 'next to Window',
            'price_per_week' => 25.50
        ]);

        $response->assertStatus(200);
    }

    public function test_room_manager_cant_update_desk()
    {
        $user = User::factory()->create([
            'role' => 'room manager'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $this->post('api/desks',[
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'small',
            'room_id' => 1,
        ]);

        $response = $this->put('api/desks/1',[
            'position' => 'next to Window',
            'price_per_week' => 25.50
        ]);

        $response->assertStatus(401);
    }
}
