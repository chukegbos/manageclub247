<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\FoodInventory;

class MarketItem extends Model
{
    use SoftDeletes;
    protected $table = 'market_items';

    protected $fillable = [
        'item', 'market_id', 'amount', 'quantity'
    ];

    protected $dates = [
        'deleted_at', 
    ];

    public function getItemAttribute()
    {
        $id = $this->attributes['item'];

        $food = FoodInventory::find($id);
     
        if ($food) {
            return $food->name;      
        }

        else{
            return '---';
        }
    }
}
