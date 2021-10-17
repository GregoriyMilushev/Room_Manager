<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Room;
use App\Models\Desk;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class UserTest extends TestCase
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

   public function test_admin_gets_all_users()
   {
       $response = $this->actingAs($this->admin)->get('api/users');

       $response->assertOk();
       $response->assertSee('Joro');
   }

   public function test_admin_get_a_user()
   {
       $response = $this->actingAs($this->admin)->get('api/users/' . $this->client->id);

       $response->assertOk();
       $response->assertSee('Joro');
   }

   public function test_admin_delete_user()
   {
       $response = $this->actingAs($this->admin)->delete('api/users/' . $this->client->id);

       $response->assertOk();
       $this->assertCount(2, User::all());
   }

   public function test_admin_cant_delete_admin()
   {
       $response = $this->actingAs($this->admin)->delete('api/users/' . $this->admin->id);

       $response->assertStatus(403);
       $response->assertSee('Not allowed to delete Admin user');
       $this->assertCount(3, User::all());
   }

   public function test_deleting_manager_change_manager_of_his_room()
   {
       $response = $this->actingAs($this->admin)->delete('api/users/' . $this->manager->id);
       
        $data = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertCount(2, User::all());
        $this->assertTrue($data->manager_id == $this->admin->id);
   }

   public function test_deleting_client_with_rent_change_user_of_his_desk()
   {
        Desk::factory()->create([
            'is_taken' => true,
            'user_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->admin)->delete('api/users/' . $this->client->id);

        $desk = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertCount(2, User::all());
        $this->assertTrue($desk->is_taken == false);
   }

   public function test_admin_cant_update_admin_user()
   {
        $response = $this->actingAs($this->admin)->patch('api/users/' . $this->admin->id,[
            'name' => 'John',
            'role' => 'client'
        ]);

        $response->assertStatus(403);
        $response->assertSee('Not allowed to update a Admin user');
   }

   public function test_admin_cant_update_user_name_lower_than_three_chars()
   {
        $response = $this->actingAs($this->admin)->patch('api/users/' . $this->client->id,[
            'name' => 'Jo',
            'role' => 'client'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
   }

   public function test_admin_can_update_user_name()
   {
        $response = $this->actingAs($this->admin)->patch('api/users/' . $this->client->id,[
            'name' => 'Johni',
            'role' => 'client'
        ]);

        $user = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertTrue($user->name == 'Johni');
   }

   public function test_admin_can_update_user_role_to_client_form_manager()
   {
        $response = $this->actingAs($this->admin)->patch('api/users/' . $this->manager->id,[
            'role' => 'client'
        ]);

        $manager_room = Room::find($this->room->id);
        //$user = json_decode($response->getContent(), true);
        $user = $response->getData();

        $response->assertStatus(200);
        $this->assertTrue($user->role == 'client');
        $this->assertTrue($manager_room->manager_id ==  $this->admin->id);
   }

   public function test_manager_cant_rent_desk()
   {
        $response = $this->actingAs($this->manager)->patch('api/users/rent/' . $this->desk->id,[
            'rented_weeks' => 2
        ]);

        $response->assertStatus(403);
        $response->assertSee('Only clients are allowed to rent a desk');
   }

   public function test_client_can_rent_desk()
   {
        $response = $this->actingAs($this->client)->patch('api/users/rent/' . $this->desk->id,[
            'rented_weeks' => 2
        ]);

        $desk = $response->getData();

        $response->assertStatus(200);
        $this->assertTrue($desk->data->attributes->client->user->id == $this->client->id);
   }

   public function test_client_cant_rent_taken_desk()
   {
        $desk = Desk::factory()->create([
            'is_taken' => true,
            'user_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->client)->patch('api/users/rent/' . $desk->id,[
            'rented_weeks' => 2
        ]);

        $response->assertStatus(403);
        $response->assertSee('Desk is allready taken.');
   }

   public function test_client_cant_rent_desk_under_one_week()
   {
        $response = $this->actingAs($this->client)->patch('api/users/rent/' . $this->desk->id,[
            'rented_weeks' => 0.5
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rented_weeks');
   }

   public function test_client_can_see_rent_prices()
   {
        $desk = Desk::factory()->create([
            'is_taken' => true,
            'user_id' => $this->client->id,
            'rented_weeks' => 3,
        ]);

        $response = $this->actingAs($this->client)->post('api/users/price');

        $prices = json_decode($response->content());

        $total_price = number_format($desk->rented_weeks * $desk->price_per_week, 2);

        $response->assertStatus(200);
        $this->assertTrue($prices->data->total_price == $total_price);
   }
}
