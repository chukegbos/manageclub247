<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\Kitchen;
use App\InventoryStore;

class ServiceItem extends Model
{
    use SoftDeletes;
    protected $table = 'service_items';

    protected $fillable = [
        'code', 'amount', 'qty', 'food', 'kitchen', 'status', 'main_kitchen', 'paid', 'available'
    ];

    protected $dates = [
        'deleted_at', 
    ];

    public function getFoodAttribute()
    {
        $food_id = $this->attributes['food'];
        $food = Food::find($food_id);
        if ($food) {
            return $food->name;        
        }

        else{
            return '---';
        }
    }

    public function getKitchenAttribute()
    {
        $kitchen_id = $this->attributes['kitchen'];
        $kitchen = FoodKitchen::find($kitchen_id);
        if ($kitchen) {
            return $kitchen->status;        
        }

        else{
            return '---';
        }
    }

    public function getAvailableAttribute()
    {
        $kitchen_id = $this->attributes['kitchen'];
        $kitchen = FoodKitchen::find($kitchen_id);
        if ($kitchen) {
            return $kitchen->number;        
        }

        else{
            return '---';
        }
    }
}