<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Desk;
use App\Models\Room;

class DeskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Desk::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $latest_room_id = Room::latest()->first()->id;

        $request->validate([
            'price_per_week' => 'required|numeric|between:0.00,99.99',
            'size' => 'required|in:small,big',
            'position' => 'required|string|max:250',
            'room_id' => 'required|numeric|between:1,'. $latest_room_id,
        ]);

        $room = Room::where('id',$request['room_id'])->first();
        $desks_count = $room->desk->count();

        if ($room->desk_capacity <= $desks_count) {
            return response([
                'message' => 'Room is allready Full!'
            ],403);
        }

        return Desk::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        return Desk::find($id);
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

        return $desk;
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
       return Desk::where('position', 'like', '%'.$position.'%')->get();
    }

     
}
