<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\DeskResource;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserRequest;
use App\Http\Requests\RentRequest;
use App\Models\Desk;
use App\Models\Room;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
         $this->middleware('auth:sanctum');
         $this->middleware('admin')->only(['index', 'update', 'destroy', 'show']);
    }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserResource::collection(User::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, User $user)
    {
        return new UserResource($user);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $admin = auth()->user();

        $manager_at_room = $user->rooms->first();
        $user_desk = $user->desk;

        if ($manager_at_room) {
            $manager_at_room->manager_id = $admin->id;
            $manager_at_room->save();
        }

        if ($user_desk) {
            $user_desk->is_taken = false;
            $user_desk->user_id = null;
            $user_desk->rented_weeks = null;
            $user_desk->rented_at = null;
            $user_desk->rent_until = null;
            $user_desk->save();
        }

        return User::destroy($user->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {   // Another admin cant be updated
        // if ($user[role] == 'admin') {
        //     return response([
        //         'message' => 'Not allowed to update a Admin'
        //     ],403);
        // }

        $user->update($request->all());
        
        // Make another admin?
        // if ($request['role']) {

        //     $user->role = $request['role'];
        //     $user->save();
        // }

        return new UserResource($user);
    }
    
    /**
     * Rent a free desks.
     *
     * @return \Illuminate\Http\Response
     */
    public function rent(RentRequest $request, $desk_id)
    {
        $desk = Desk::find($desk_id);
        $user = auth()->user();

        if ($user['role'] != 'client') {
            return response([
                'message' => 'Only clients are allowed to rent a desk'
            ], 403);
        }

        if ($desk->is_taken == false) {

            $desk->is_taken = true;
            $desk->user_id = auth()->user()->id;
            $desk->rented_weeks = $request['rented_weeks'];
            $desk->rented_at = now();
            $desk->rent_until = now()->addWeeks($request['rented_weeks']);

            $desk->save();
        }
        else {
            return response([
                'message' => 'Desk is allready taken.'
            ], 403);
        }

        return new DeskResource($desk);
    }

    /**
     * Display total price.
     *
     * @return \Illuminate\Http\Response
     */
    public function price()
    {
        $desk = auth()->user()->desk;
        if (!$desk) {
            return response([
                'message' => 'There is no rented desk'
            ], 404);
        }

        $rented_weeks = $desk->rented_weeks;
        $price_per_week = $desk->price_per_week;
        $total_price = number_format($rented_weeks * $price_per_week, 2);

        $response = [
            'data' =>[
                'rented_weeks' => $rented_weeks,
                'price_per_week' => $price_per_week,
                'total_price' => $total_price,
            ]
        ];

        return response($response, 200);
    }
}
