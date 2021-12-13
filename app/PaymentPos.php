<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class PaymentPos extends Model
{
	use SoftDeletes;
	protected $table = 'system_pos';
    protected $fillable = [
    	'name', 'code', 'channel'
    ];
}
