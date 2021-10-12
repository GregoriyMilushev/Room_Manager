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
       $response->assertSee('Cannot delete Admin user');
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

   public function test_admin_update_user()
   {
       
   }
}
