<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelRequestModel extends Model
{
    protected $table = 'travel_requests';

    protected $fillable = [
        'requester_name',
        'destination',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
