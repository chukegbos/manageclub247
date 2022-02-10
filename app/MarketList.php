<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\InventoryStore;

class MarketList extends Model
{
    use SoftDeletes;
    protected $table = 'market_lists';

    protected $fillable = [
        'purchase_date', 'market_id', 'user_id', 'amount', 'status'
    ];

    protected $dates = [
        'deleted_at', 
    ];

    public function getUserIdAttribute()
    {
        $id = $this->attributes['user_id'];
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
}
