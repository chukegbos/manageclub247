<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\Inventory;
use App\InventoryStore;

class Inventory extends Model
{
	use SoftDeletes;
	
    protected $fillable = [
    	'product_id', 'product_name', 'cost_price', 'price', 'quantity', 'category', 'unit', 'threshold', 'number_per_crate'
    ];

    public function getQuantityAttribute()
    {
        $id = $this->attributes['id'];
        $inventory = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $id)->where('store_id', 1)->first();
        if($inventory){
            return $inventory->number;
        }
        else{
            return NULL;
        }
    }

}
