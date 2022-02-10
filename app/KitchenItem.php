<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\Inventory;
use App\Category;

class KitchenItem extends Model
{
    use SoftDeletes;
    protected $table = 'kitchen_items';
   
    protected $fillable = [
        'item', 'kitchen_id', 'status', 'number'
    ];


    protected $dates = [
        'deleted_at', 'updated_at'
    ];
}
