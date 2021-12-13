<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;

class DebtorHistory extends Model
{
    use SoftDeletes;
    protected $table = 'debtor_history';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'debtor_id', 'amount', 'amount_paid', 'purchase_date', 'processed_by', 'store_id'
    ];


    protected $dates = [
        'deleted_at', 'updated_at'
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
        $store_id = $this->attributes['store_id'];
        $store = Store::where('deleted_at', NULL)->find($store_id);
        if (isset($store)) {
            return $store->name;
        }
        else{
            return '---';
        }
    }
}
