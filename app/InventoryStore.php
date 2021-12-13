<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\Inventory;
use App\Category;

class InventoryStore extends Model
{
    use SoftDeletes;
    protected $table = 'inventory_store';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'inventory_id', 'number', 'category'
    ];


    protected $dates = [
        'deleted_at', 'updated_at'
    ];

    public function getUpdatedAtAttribute()
    {
        $updated_at = $this->attributes['updated_at'];
        $date2 = date_create(Carbon::parse($updated_at)->toDateString()); 
    
        //$date2=date_create("2017-03-15");
        $date1 = date_create(date('Y-m-d'));
        $diff = date_diff($date2, $date1);
        if ($diff->days>0) {
            return $diff->days.' days';
        }

        
    }
}
