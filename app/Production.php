<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\FoodKitchen;

class Production extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'production_id', 'production_date', 'product', 'quantity'
    ];

    protected $hidden = [
      'remember_token', 'deleted_at',
    ];

    protected $dates = ['production_date'];

    public function getProductAttribute()
    {
        $product_id = $this->attributes['product'];

        $food = FoodKitchen::find($product_id);
      
        if ($food) { 
            return $food->status;     
        }

        else{
            return '---';
        }
    }
}
