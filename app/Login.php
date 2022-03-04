<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Kitchen;
use App\Store;

class Login extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'logout', 'user_id', 'status', 'verified_by', 'kitchen_id', 'store', 'kitchen'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'deleted_at', 
    ];

    public function getVerifiedByAttribute()
    {
        $id = $this->attributes['verified_by'];
        
      
        $user = User::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->name;
        }
        else{
            return null;
        }
    }

    public function getStoreAttribute()
    {
        if (isset($this->attributes['store_id'])) {

            $id = $this->attributes['store_id'];
            if ($id) {
                $store = Store::where('deleted_at', NULL)->find($id);
                if ($store) {
                    return $store->name;
                }
                else{
                    return "---";
                }
            }
            else{
                return "---";
            }
        }
    }


    public function getKitchenAttribute()
    {
        if (isset($this->attributes['kitchen_id'])) {

            $id = $this->attributes['kitchen_id'];
            if ($id) {
                $kitchen = Kitchen::where('deleted_at', NULL)->find($id);
                if ($kitchen) {
                    return $kitchen->name;
                }
                else{
                    return "---";
                }
            }
            else{
                return "---";
            }
        }
    }
}