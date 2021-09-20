<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Desk;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'desk_capacity',
        'size',
    ];

    public function user()
    {
        return $this->belongTo(User::class, 'manager_id');
    }

    public function desk()
    {
        return $this->hasMany(Desk::class);
    }
}
