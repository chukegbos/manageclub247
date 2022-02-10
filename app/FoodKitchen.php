<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Food;

class FoodKitchen extends Model
{
    use SoftDeletes;
    protected $table = 'food_kitchen';

    protected $fillable = [
        'kitchen_id', 'food_id', 'number', 'status', 'period'
    ];

    protected $dates = [
        'deleted_at', 'updated_at'
    ];

    public function getStatusAttribute()
    {
        $status = $this->attributes['status'];
        $food_id = $this->attributes['food_id'];
        $kitchen_id = $this->attributes['kitchen_id'];
        $period = $this->attributes['period'];

        $food = Food::find($food_id);
        $kitchen = Kitchen::find($kitchen_id);
        if ($food && $kitchen) {
            /*if ($status==1) {
                return $food->name.' ('.$kitchen->name.') -Ready';
            }
            else{
                return $food->name.' ('.$kitchen->name.') -Available in '. $period .' Minutes';
            }*/ 
            return $food->name.' ('.$kitchen->name.')';     
        }

        else{
            return '---';
        }
    }
}
