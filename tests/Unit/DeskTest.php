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
    use RefreshDatabase;

  
    /**
     * Set up the test
     */
    // public function setUp(): void
    // {
    //     parent::setUp();
    //     $this->admin =  User::factory()->create([
    //         'role' => 'admin'
    //     ]);

    //     $this->manager = User::factory()->create([
    //         'role' => 'room manager',
    //     ]);
    // }
    
    public function test_admin_getting_all_desks()
    {
        // dd($this->admin);

        $this->actingAs($this->admin);

        $response = $this->get('api/desks');

        $response->assertOk();
        // $response->assertStatus(200);
    }

    public function test_room_manager_can_only_sees_his_room_desks()
    {
        $this->withoutExceptionHandling();

        $manager = User::factory()->create([
            'role' => 'room manager',
        ]);

        $this->actingAs($this->manager);

        $room = Room::factory()->create();

        $desks = Desk::factory(3)->create([
            'room_id' => 1,
        ]);

        $response = $this->get('api/desks');
        $response->assertStatus(200);
        $this->assertCount(3,$room->desks);
        //$response->assertJson(['room_id' => '1']);
    }

    public function test_client_sees_his_rented_desk()
    {
        $manager = User::factory()->create([
            'role' => 'room manager'
        ]);

        $client = User::factory()->create();

        $this->actingAs($client);

        $room = Room::factory()->create([
            'manager_id' => 2
        ]);

        $desk = Desk::factory()->create([
            'is_taken' => true,
            'user_id' => 2,
        ]);

        $response = $this->get('api/desks');
        $response->assertStatus(200);
        $this->assertTrue($desk['position'] == $client->desk['position']);
    }    
    
    public function test_clien_cant_sees_other_than_rented_desk()
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
    }      

    public function test_admin_create_desk()
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $response = $this->post('api/desks',$this->desk_data());

        $response->assertStatus(201);
        $this->assertCount(1,Desk::all());
    }
    
    /** @test */
    public function test_admin_cant_create_desk_in_uncreated_room()
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $response = $this->post('api/desks',array_merge($this->desk_data(), ['room_id' => 2]));

        $response->assertSessionHasErrors('room_id');
        $this->assertCount(0,Desk::all());
    }

    public function test_client_cant_store_desk()
    {
        $client = User::factory()->create();

        $this->actingAs($client);

        $room = Room::factory()->create();

        $response = $this->post('api/desks',$this->desk_data());

        $response->assertStatus(401);
        $this->assertCount(0,Desk::all());
    }

    public function test_admin_cant_create_different_than_small_or_big_desk_size()
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user);

        $room = Room::factory()->create();

        $response = $this->post('api/desks',array_merge($this->desk_data(),['size' => 'large']));

        $response->assertSessionHasErrors('size');
        $this->assertCount(0, Desk::all());
    }

    public function test_full_room_cant_store_desk()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $room = Room::factory()->create([
            'desk_capacity' => 1
        ]);

        for ($i=0; $i <= 1; $i++) { 
            $response = $this->post('api/desks',$this->desk_data()); 
        }

        $response->assertStatus(403);
        $this->assertCount(1, Desk::all());
    }

    public function test_admin_delete_desk()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($admin);

        $room = Room::factory()->create();

        $this->post('api/desks',$this->desk_data());

        $response = $this->delete('api/desks/1');

        $response->assertStatus(200);
        $this->assertCount(0 ,Desk::all());
    }

    public function test_room_manager_cant_delete_desk()
    {
        $manager = User::factory()->create([
            'role' => 'room manager'
        ]);

        $this->actingAs($manager);

        $room = Room::factory()->create();

        Desk::factory()->create();

        $response = $this->delete('api/desks/1');

        $response->assertStatus(401);
        $this->assertCount(1 ,Desk::all());
    }

    public function test_admin_updating_desk()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($admin);

        $room = Room::factory()->create();

        $this->post('api/desks',$this->desk_data());

        $response = $this->put('api/desks/1',[
            'position' => 'next to Window',
            'price_per_week' => 25.50
        ]);

        $response->assertStatus(200);
        $response->assertSee('next to Window');
    }

    public function test_room_manager_cant_update_desk()
    {
        $manager = User::factory()->create([
            'role' => 'room manager'
        ]);

        $this->actingAs($manager);

        $room = Room::factory()->create();

        $this->post('api/desks',$this->desk_data());

        $response = $this->put('api/desks/1',[
            'position' => 'next to Window',
            'price_per_week' => 25.50
        ]);

        $response->assertStatus(401);
    }

    private function desk_data()
    {
        return [
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'small',
            'room_id' => 1,
        ];
    }
}
