<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\FoodInventory;

class FoodInventory extends Model
{
	use SoftDeletes;
	protected $table = 'food_inventory';
    protected $fillable = [
    	'name', 'amount', 'unit', 'quantity'
    ];
}
