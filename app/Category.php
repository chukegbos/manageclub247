<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\Inventory;

class Category extends Model
{
	use SoftDeletes;
	
    protected $fillable = [
    	'name', 'inventory'
    ];

    public function getInventoryAttribute()
    {
        $id = $this->attributes['id'];
        return Inventory::where('deleted_at', NULL)->where('deleted_at', NULL)->where('category', $id)->count();
    }
}
