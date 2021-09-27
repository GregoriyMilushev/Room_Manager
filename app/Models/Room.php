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
        'size',
        'desk_capacity',
        'manager_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function desks()
    {
        return $this->hasMany(Desk::class);
    }
}
