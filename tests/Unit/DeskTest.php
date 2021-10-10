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
    public function setUp(): void
    {
        parent::setUp();

        $this->admin =  User::factory()->create([
            'role' => 'admin'
        ]);

        $this->manager = User::factory()->create([
            'role' => 'room manager',
        ]);

        $this->client = User::factory()->create();

        $this->room = Room::factory()->create([
           'manager_id' => $this->manager->id
        ]);
    }
    
    public function test_admin_getting_all_desks()
    {
        $desks = Desk::factory(5)->create([]);

        $response = $this->actingAs($this->admin)->get('api/desks');

        $response->assertOk();
    }

    public function test_admin_getting_one_desk()
    {
        $desks = Desk::factory()->create();

        $response = $this->actingAs($this->admin)->get('api/desks/1');

        $response->assertOk();
    }

    public function test_client_get_rented_desk()
    {
        $desk = Desk::factory()->create([
            'is_taken' => true,
            'user_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->client)->get('api/desks');

        $response->assertOk();
        $response->assertSee($this->client->name);
        $response->assertSee($desk->position);
        $this->assertTrue($desk['position'] == $this->client->desk['position']);
    }

    public function test_client_cant_get_unrented_desk()
    {
        $desk = Desk::factory()->create();

        $response = $this->actingAs($this->client)->get('api/desks');

        $response->assertStatus(403);
        $response->assertSee('You have not rented a desk');
    }

    public function test_room_manager_gets_his_room_desks()
    {
        $desk = Desk::factory(5)->create();

        $response = $this->actingAs($this->manager)->get('api/desks');

        $response->assertOk();
        $this->assertCount(5, $this->manager->rooms->first()->desks);
    }            

    public function test_admin_create_desk()
    {
        $response =  $this->actingAs($this->admin)->post('api/desks',$this->desk_data());

        $response->assertStatus(201);
        $this->assertCount(1,Desk::all());
    }
    
    /** @test */
    public function test_admin_cant_create_desk_in_uncreated_room()
    {
        $response = $this->actingAs($this->admin)
                    ->post('api/desks',array_merge($this->desk_data(), ['room_id' => 2]));

        $response->assertSessionHasErrors('room_id');
        $this->assertCount(0,Desk::all());
    }

    public function test_client_cant_store_desk()
    {
        $response = $this->actingAs($this->client)->post('api/desks',$this->desk_data());

        $response->assertStatus(401);
        $this->assertCount(0,Desk::all());
    }

    public function test_admin_cant_create_different_than_small_or_big_desk()
    {
        $response = $this->actingAs($this->admin)
                    ->post('api/desks',array_merge($this->desk_data(),['size' => 'large']));

        $response->assertSessionHasErrors('size');
        $this->assertCount(0, Desk::all());
    }

    public function test_desk_price_per_week_cant_be_zero()
    {
        $response = $this->actingAs($this->admin)
                    ->post('api/desks',array_merge($this->desk_data(),['price_per_week' => 0]));

        $response->assertSessionHasErrors('price_per_week');
        $this->assertCount(0, Desk::all());
    }

    public function test_desk_price_per_week_cant_be_negative()
    {
        $response = $this->actingAs($this->admin)
                    ->post('api/desks',array_merge($this->desk_data(),['price_per_week' => -5]));

        $response->assertSessionHasErrors('price_per_week');
        $this->assertCount(0, Desk::all());
    }

    public function test_full_room_cant_store_desk()
    {
        $response;

        for ($i=0; $i <= $this->room->desk_capacity; $i++) { 
            $response = $this->actingAs($this->admin)->post('api/desks',$this->desk_data()); 
        }

        $response->assertStatus(403);
        $response->assertSee('Room is allready Full!');
        $this->assertCount($this->room->desk_capacity, Desk::all());
    }

    public function test_admin_delete_desk()
    {
        $desk = Desk::factory()->create();

        $response = $this->actingAs($this->admin)->delete('api/desks/1');

        $response->assertStatus(200);
        $this->assertCount(0 ,Desk::all());
    }

    public function test_room_manager_cant_delete_desk()
    {
        Desk::factory()->create();

        $response = $this->actingAs($this->manager)->delete('api/desks/1');

        $response->assertStatus(401);
        $this->assertCount(1 ,Desk::all());
    }

    public function test_admin_updating_desk()
    {
        $this->actingAs($this->admin);

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
        $desk = Desk::factory()->create();

        $response = $this->actingAs($this->manager)->put('api/desks/1',[
            'position' => 'next to Window',
            'price_per_week' => 25.50
        ]);

        $response->assertStatus(401);
    }

    public function test_every_user_can_get_available_desks()
    {
        $desk = Desk::factory(5)->create();

        $response = $this->actingAs($this->admin)->post('api/desks/available');
        $response->assertOk();

        $response = $this->actingAs($this->manager)->post('api/desks/available');
        $response->assertOk();

        $response = $this->actingAs($this->client)->post('api/desks/available');
        $response->assertOk();
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
