<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Room;

class DesksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $latest_room_id = Room::latest()->first()->id;

        return [    
            'price_per_week' => 'required|numeric|between:0.00,99.99',
            'size' => 'required|in:small,big',
            'position' => 'required|string|max:250',
            'room_id' => 'required|numeric|between:1,'. $latest_room_id,
        ];
    }
}
