<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\User;

class ProductType extends Model
{
    protected $table = 'product_type';
    protected $fillable = [
        'product_id', 'type_id'
    ];
}
