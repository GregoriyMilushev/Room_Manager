<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desk extends Model
{
    use HasFactory;

    protected $table = 'desks';

    protected $fillable = [
        'price_per_week',
        'size',
        'position'
    ];
}
