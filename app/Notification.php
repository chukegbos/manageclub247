<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Notification extends Model
{
	use SoftDeletes;
	
    protected $fillable = [
    	'store_id', 'module_id', 'purpose', 'status'
    ];

}
