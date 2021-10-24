<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\Desk;
use App\Http\Resources\UserResource;
use App\Http\Resources\RoomResource;
use App\Http\Requests\RoomRequest;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class RoomController extends Controller
{

    public function __construct()
    {
         $this->middleware('auth:sanctum');
         $this->middleware('admin')->only(['store', 'update', 'destroy', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role == 'room manager') {

            $room = Room::where('manager_id', $user->id)->first();
            
            return new RoomResource($room);
        }
        else if ($user->role == 'admin') {

            return RoomResource::collection(Room::paginate(2));
        }
        else {

            return response([
                'message' => 'Clients are not allowed'
            ],403);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoomRequest $request)
    {
        $desk_capacity = 0;
        $size = $request->size;

        if ($size == 'small') {
            $desk_capacity = 10;
        }
        elseif ($size = 'big') {
            $desk_capacity = 15;
        }
        
        $room = Room::create([
            'desk_capacity' => $desk_capacity,
            'size' => $request['size'],
            'manager_id' => $request['manager_id'] ?: auth()->user()->id,
        ]);

        return new RoomResource($room);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,Room $room)
    {
        return new RoomResource($room);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function edit(Room $room)
    {
        //
    }

    /**
     * Update room manager.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Room $room)
    {
        if ($request['manager_id']) {
            
            $old_manager = User::find($room->manager_id);
            $new_manager = User::where('id',$request['manager_id'])->first();
        
            if (!$new_manager) {
                return response([
                    'message' => 'User does not exists'
                ], 404);
            }

            $is_taken = Room::where('manager_id', $new_manager->id)->first() ? true : false;

            if ($is_taken && $new_manager->role != 'admin') {
                return response([
                    'message' => 'Manager is allready taken'
                ], 403);
            }

            $this->updateRoomManager($old_manager, $new_manager, $room);
        }

        $room->update($request->all());

        return new RoomResource($room);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy(Room $room)
    {
        $room_manager = $room->user;
        $desks = $room->desks;

        if ($room_manager->role != 'admin') {

            $room_manager->role = 'client';
            $room_manager->save();
        }

        Desk::destroy($desks);
        Room::destroy($room->id);

        return response([
            'message' =>'Successfully deleted Room and Desks',
            'user' => new UserResource($room_manager)
        ], 200);
    }

    private function updateRoomManager(User $old_manager, User $new_manager, Room $room): void
    {
        $room->manager_id = $new_manager->id;
        $room->save();

        if ($new_manager->role != 'admin') {

            $new_manager->role = 'room manager';
            $new_manager->save();
        }
        
        if ($old_manager->role != 'admin') {

            $old_manager->role = 'client';
            $old_manager->save();
        }
    }
}
