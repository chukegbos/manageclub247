<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\InventoryStore;
use App\Inventory;

class RoomMovement extends Model
{
    use SoftDeletes;
    protected $table = 'rooom_movement';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'product_id', 'qty', 'user_id', 'ref_id', 'status', 'move_type', 'moved', 'manager_id', 'approved_by', 'title', 'type', 'available'
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
        $store_id = $this->attributes['moved'];
        $product_id = $this->attributes['product_id'];
        $owner = InventoryStore::where('deleted_at', NULL)->find($product_id);
        if ($owner) {
            $product = Inventory::where('deleted_at', NULL)->find($owner->inventory_id);
            if ($product) {
                $id = $product->id; 
                $main = InventoryStore::where('deleted_at', NULL)->where('store_id', 1)->where('inventory_id', $id)->first();
                if ($main) {
                    return $main->number;
                }
                else{
                    return '0';
                }             
            }
            else{
                return '0';
            }
        }
    }

    public function getMainProductAttribute()
    {
        $product_id = $this->attributes['product_id'];
        $owner = InventoryStore::where('deleted_at', NULL)->find($product_id);
        if ($owner) {
            $product = Inventory::where('deleted_at', NULL)->find($owner->inventory_id);
            if ($product) {
                $id = $product->id; 
                $crate = $product->number_per_crate;

                $main = InventoryStore::where('deleted_at', NULL)->where('store_id', 1)->where('inventory_id', $id)->first();
                if ($main) {
                    $float = $main->number/$crate;
                    $parts = explode('.', (string)$float);
                    return $parts[0];
                }
                else{
                    return '0';
                }             
            }
            else{
                return '0';
            }
        }
    }

    public function getTitleAttribute()
    {
        $product_id = $this->attributes['product_id'];

        $owner = InventoryStore::where('deleted_at', NULL)->find($product_id);
        if ($owner) {
            $product = Inventory::where('deleted_at', NULL)->find($owner->inventory_id);
            if ($product) {
                return $product->product_name; 
            }
            else{
                return '---';
            }
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
