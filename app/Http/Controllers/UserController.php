<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\DeskResource;
use App\Models\Desk;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
         $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the free desks.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $available_desks = Desk::where('is_taken', false)->get();
        return DeskResource::collection($available_desks);
    }

     /**
     * Rent a free desks.
     *
     * @return \Illuminate\Http\Response
     */
    public function rent(Request $request, $desk_id)
    {
        $desk = Desk::find($desk_id);

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
                'massage' => 'Desk is allready taken.'
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

        $rented_weeks = auth()->user()->desk->rented_weeks;
        $price_per_week = auth()->user()->desk->price_per_week;
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
