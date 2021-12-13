<?php

namespace App;

use App\User;
use App\Store;
use App\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
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
        'purchase_id', 'date_of_purchase', 'product', 'amount_paid', 'total_price', 'balance', 'supplier', 'quantity', 'purchase_date', 'cost_price_yuan', 'store_id', 'mop', 'type', 'status', 'status_accept', 'initiated_by', 'accepted_by', 'approved_by', 'featured_total_price'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'remember_token', 'deleted_at', 'date_of_purchase'
    ];

    protected $dates = ['date_of_purchase'];
    public function getBalanceAttribute()
    {
        $balance = $this->attributes['total_price'] - $this->attributes['amount_paid'];
        return $balance;
    }

    public function getStoreIdAttribute()
    {
        $store_id = $this->attributes['store_id'];
        $store = Store::where('deleted_at', NULL)->find($store_id);
        if (isset($store)) {
            return $store->name;
        }
        else{
            return '---';
        }
    }

    public function getTypeAttribute()
    {
        $type = $this->attributes['type'];
        if ($type==1) {
            return 'Inventory Purchase';
        }
        else{
            return 'Other Purchase';
        }
    }

    public function getSupplierAttribute()
    {
        $supplier_id= $this->attributes['supplier'];
        $supplier = Supplier::where('deleted_at', NULL)->find($supplier_id);
        if (isset($supplier)) {
            return $supplier->supplier_name;
        }
        else{
            return '---';
        }
    }

    public function getMopAttribute()
    {
        $mop = $this->attributes['mop'];
        
        if ($mop == 1) {
            return 'Cash Payment';
        }
        else
        {
            return 'Credit Payment';
        }
    }

    public function getInitiatedByAttribute()
    {
        $id = $this->attributes['initiated_by'];
        if ($id) {
            $initiator = User::where('deleted_at', NULL)->find($id);
            if ($initiator) {
                return $initiator->name;
            }
            else
            {
                return '---';
            }
        }
        else
        {
            return '---';
        }
    }

    public function getAcceptedByAttribute()
    {
        $id = $this->attributes['accepted_by'];
        if ($id) {
            $reciever_id = User::where('deleted_at', NULL)->find($id);
            if ($reciever_id) {
                return $reciever_id->name;
            } 
            else
            {
                return '---';
            }
        }
        else
        {
            return '---';
        }
        
    }

    public function getApprovedByAttribute()
    {
        $id = $this->attributes['approved_by'];
        if ($id) {
            $approved_by = User::where('deleted_at', NULL)->find($id);
            if ($approved_by) {
                return $approved_by->name;
            }
             else
            {
                return '---';
            }
        }
        else
        {
            return '---';
        }
    }
}
