<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;
use App\InventoryStore;

class ServiceItem extends Model
{
    use SoftDeletes;
    protected $table = 'service_items';

    protected $fillable = [
        'code', 'price', 'title'
    ];

    protected $dates = [
        'deleted_at', 
    ];
}
