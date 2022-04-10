<?php

namespace App;

use App\User;
use App\Inventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Item extends Model
{
    /*public function user(){
    	return $this->belongsTo('App\User');
    }
    */
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'product_id', 'totalPrice', 'product_name', 'qty', 'price', 'discount'
    ];

    protected $dates = [
        'deleted_at', 'main_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'remember_token', 'deleted_at'
    ];

    public function getProductNameAttribute()
    {
        $id = $this->attributes['product_id'];
        $inventory = Inventory::where('deleted_at', NULL)->find($id);
        if($inventory){
            return $inventory->product_name;
        }
        else{
            return NULL;
        }
    }
  }
