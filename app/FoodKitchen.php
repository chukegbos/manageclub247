<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodKitchen extends Model
{
    use SoftDeletes;
    protected $table = 'food_kitchen';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kitchen_id', 'food_id', 'number',
    ];


    protected $dates = [
        'deleted_at', 'updated_at'
    ];
}
