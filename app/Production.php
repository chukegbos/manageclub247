<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'production_id', 'production_date', 'product', 'quantity'
    ];

    protected $hidden = [
      'remember_token', 'deleted_at',
    ];

    protected $dates = ['production_date'];
}
