<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\InventoryStore;

class ItemPurchase extends Model
{
    use SoftDeletes;
    protected $table = 'item_purchases';

    protected $fillable = [
        'purchase_id', 'product_id', 'qty', 'cost', 'total', 'title', 'featured_cost'
    ];

    protected $dates = [
        'deleted_at', 
    ];
}
