<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'last_change_date' => 'date',
        'date_close' => 'date',
    ];
}
