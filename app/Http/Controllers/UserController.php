<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Desk;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the free desks.
     *
     * @return \Illuminate\Http\Response
     */
    public function available()
    {
        return Desk::where('is_taken', false)->get();
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
            return [
                'massage' => 'Desk is allready taken.'
            ];
        }

        return Desk::where('id', $desk_id)->get();
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
            'rented_weeks' => $rented_weeks,
            'price_per_week' => $price_per_week,
            'total_price' => $total_price,
        ];

        return response($response, 200);
    }
}
