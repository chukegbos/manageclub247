<?php

namespace App;

use App\User;
use App\Supplier;
use App\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Debtor extends Model
{
    /*public function user(){
    	return $this->belongsTo('App\User');
    }
    */
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * type
        0-purchase
        1-sales
     */
    protected $fillable = [
        'trans_id', 'user_id', 'status', 'amount', 'amount_paid', 'repayment_date', 'type', 'supplier_id', 'processed_by', 'store_id'
    ];

    protected $dates = [
        'deleted_at'
    ];

    
    protected $hidden = [
      'remember_token', 'deleted_at'
    ];

    public function getProcessedByAttribute()
    {
        $id = $this->attributes['processed_by'];
        
      
        $user = User::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->name;
        }
        else{
            return '---';
        }
    }

    public function getStoreIdAttribute()
    {
        $id = $this->attributes['store_id'];
        
      
        $store = Store::where('deleted_at', NULL)->find($id);
        if ($store) {
            return $store->name;
        }
        else{
            return '---';
        }
    }
    public function getUserIdAttribute()
    {
        $id = $this->attributes['user_id'];
        
      
        $user = User::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->name;
        }
        else{
            return '---';
        }
    }

    public function getSupplierIdAttribute()
    {
        $id = $this->attributes['supplier_id'];
        
      
        $user = Supplier::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->supplier_name;
        }
        else{
            return '---';
        }
    }

    /*public function getAmountAttribute()
    {
        $amount = $this->attributes['amount'];
        return ((7.5/100) * $amount) + $amount;
    }*/

  }
