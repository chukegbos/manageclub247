<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenditureCategory extends Model
{
	use SoftDeletes;
	
    protected $fillable = [
    	'name'
    ];
}
