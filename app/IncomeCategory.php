<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;

class IncomeCategory extends Model
{
	use SoftDeletes;
	
    protected $fillable = [
    	'name'
    ];
}
