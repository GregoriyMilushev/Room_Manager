<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class DeskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (string)$this->id,
            'type' => 'Desk',
            'attributes' => [
                'price_per_week' => $this->price_per_week,
                'size' => $this->size,
                'position' => $this->position,
                'client' => $this->when($this->is_taken, [
                    'user' => new UserResource($this->user),
                    'rented_weeks' => $this->rented_weeks,
                    'rented_at' => $this->rented_at,
                    'rent_until' => $this->rent_until,
                ]),
                'room_id' => (string)$this->room_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}
