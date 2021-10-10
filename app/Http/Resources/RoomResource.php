<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\DeskResource;

class RoomResource extends JsonResource
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
            'id' => $this->id,
            'type' => 'Room',
            'attributes' => [
                'desk_capacity' => $this->desk_capacity,
                'size' => $this->size,
                'manager' => $this->user,
                'desks' => DeskResource::collection($this->desks),
            ]
        ];
    }
}
