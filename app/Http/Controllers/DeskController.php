<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Desk;
use App\Models\Room;
use App\Http\Resources\DeskResource;
use App\Http\Requests\DesksRequest;

class DeskController extends Controller
{
    public function __construct()
    {
         $this->middleware('auth:sanctum');
         $this->middleware('admin')->only(['store','update','destroy']);
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
            $room_id = $user->rooms->first()->id;
            $desks = Desk::where('room_id', $room_id)->get();

            return DeskResource::collection($desks);
        }
        else if ($user->role == 'client') {
            $desk = $user->desk;
            if ($desk == null) {
                return response([
                    'message' => 'You have not rented a desk'
                ],403);
            }

            return new DeskResource($desk);
        }

        return DeskResource::collection(Desk::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DesksRequest $request)
    {
        $room = Room::find($request['room_id']);

        $desks_count = $room->desks->count();

        if ($room->desk_capacity <= $desks_count) {
            return response([
                'message' => 'Room is allready Full!'
            ],403);
        }

        $desk = Desk::create($request->all());

        return new DeskResource($desk);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Desk $desk)
    {
        return new DeskResource($desk);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $desk = Desk::find($id);
        $desk->update($request->all());

        return new DeskResource($desk);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       return Desk::destroy($id);
    }

    /**
     * Search for position
     *
     * @param  string  $position
     * @return \Illuminate\Http\Response
     */
    public function search($position)
    {
        $desks = Desk::where('position', 'like', '%'.$position.'%')->get();

        return  DeskResource::collection($desks);
    }

     
}
