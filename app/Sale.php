<?php

namespace App;

use App\User;
use App\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Sale extends Model
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
        'sale_id', 'cart', 'totalPrice', 'percent_discount', 'initialPrice', 'discount', 'amount_paid', 'user_id', 'mop', 'buyer', 'store_id', 'main_date', 'sec_id', 'account', 'status', 'cashier_id', 'market_id', 'draft_id', 'link', 'channel', 'user_type', 'guest'
    ];

    protected $dates = [
        'deleted_at', 'main_date',
    ];

    public function getStoreIdAttribute()
    {
        $store_id = $this->attributes['store_id'];
        if ($store_id) {
            $store = Store::where('deleted_at', NULL)->find($store_id);
            if ($store) {
                return $store->name;
            }
            else {
               return '---';
            }
        }
        else {
           return '---';
        }
    }

    public function getMarketIdAttribute()
    {
        $id = $this->attributes['market_id'];
       
        $user = User::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->name;
        }
        else {
           return '---';
        }
    }

    public function getCashierIdAttribute()
    {
        $id = $this->attributes['cashier_id'];
        if ($id) {
            $user = User::where('deleted_at', NULL)->find($id);
            if ($user) {
                return $user->name;
            }
            else {
               return '---';
            }
        }
        else {
           return '---';
        }
    }

    public function getBuyerAttribute()
    {
        $id = $this->attributes['buyer'];
        if ($id) {
            $user = Member::where('deleted_at', NULL)->find($id);
            if ($user) {
                return $user->last_name. ' '. $user->first_name;
            }
            else {
               return '---';
            }
        }
        else {
            $guest = $this->attributes['guest'];
            if ($guest) {
                return $guest;
            }
            else {
                return '---';
            }
            
        }
    }

    protected $hidden = [
      'remember_token', 'deleted_at'
    ];

  }
