<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\Desk;
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

            return Room::where('manager_id', $user->id)->first();
        }
        else if ($user->role == 'admin') {
            return Room::all();
        }
        else {
            return response([
                'message' => 'Client not allowed'
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
    public function store(Request $request)
    {
        $request->validate([
            'size' => 'required|in:small,big',
        ]);

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
            'manager_id' => $request['manager_id'] ?: 1,
        ]);

        return response($room, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        return Room::find($id);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        $user = User::where('id',$request['manager_id'])->first();
        
        if ($user == null) {
            return response([
                'message' => 'User does not exists'
                ,404]);
        }

        $is_taken = Room::where('manager_id', $user->id)->first() ? true : false;

        if ($is_taken && $user->id != 1) {
            return response([
                'message' => 'Manager is allready taken'
            ], 404);
        }

        $room->manager_id = $user->id;
        $room->save();

        if ($user->id != 1) {

            $user->role = 'room manager';
            $user->save();
        }
        

        return response([
            'room' => $room,
            'user' => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $room = Room::where('id', $id)->first();
        $room_manager = $room->user;
        $desks = $room->desks;

        if ($room_manager->id != 1) {

            $room_manager->role = 'client';
            $room_manager->save();
        }

        Desk::destroy($desks);
        Room::destroy($id);

        return response([
            'message' =>'Successfully deleted Room and Desks',
            'user' => $room_manager], 200);
    }
}
