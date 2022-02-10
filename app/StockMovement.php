<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\FoodInventory;
use App\Inventory;

class StockMovement extends Model
{
    use SoftDeletes;
    protected $table = 'stock_movement';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kitchen_id', 'product_id', 'quantity', 'user_id', 'ref_id', 'status', 'approved_by', 'title', 'manager_id', 'available'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * status
     unapproved (not verify)
     pending-approved
     approved
     recieved
     rejected
        Not approved
        

     type
     0--request
     1---moving


     */

    protected $dates = [
        'deleted_at', 
    ];

    public function getAvailableAttribute()
    {
        $product_id = $this->attributes['product_id'];
        $owner = FoodInventory::where('deleted_at', NULL)->find($product_id);
        if ($owner) {
            return $owner->quantity;
        }
    }

    public function getTitleAttribute()
    {
        $product_id = $this->attributes['product_id'];

        $owner = FoodInventory::where('deleted_at', NULL)->find($product_id);
        if ($owner) {
            return $owner->name;
        }
        else{
            return '---';
        }
    }

    public function getMovedAttribute()
    {
        $moved = $this->attributes['moved'];
       
        $store = Store::where('deleted_at', NULL)->find($moved);
        if ($store) {
            return $store->name;
        }
        else{
            return 'From Purchase';
        }
    }

    public function getUserIdAttribute()
    {
        $user_id = $this->attributes['user_id'];
        if ($user_id) {
            $user = User::where('deleted_at', NULL)->find($user_id);
            if ($user) {
                return $user->name;
            }
            else{
                return '---';
            }
        }
        else{
            return '---';
        }
    }

    public function getManagerIdAttribute()
    {
        $user_id = $this->attributes['manager_id'];
        if ($user_id) {
            $user = User::where('deleted_at', NULL)->find($user_id);
            if ($user) {
                return $user->name;
            }
            else{
                return '---';
            }
        }
        else{
            return '---';
        }
    }

    public function getApprovedByAttribute()
    {
        $user_id = $this->attributes['approved_by'];
        if ($user_id) {
            $user = User::where('deleted_at', NULL)->find($user_id);
            if ($user) {
                return $user->name;
            }
            else{
                return '---';
            }
        }
        else{
            return '---';
        }
    }
}
