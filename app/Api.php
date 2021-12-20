<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Api extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'ip'
    ];

    protected $dates = [
        'deleted_at',
    ];
}
