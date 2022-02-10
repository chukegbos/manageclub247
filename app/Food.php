<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Food;

class Food extends Model
{
	use SoftDeletes;
    protected $table = 'foods';
    protected $fillable = [
    	'code', 'name', 'amount', 'status', 'period'
    ];
}
