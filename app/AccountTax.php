<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;

class AccountTax extends Model
{
    use SoftDeletes;
    protected $table = 'account_taxes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];


    protected $dates = [
        'deleted_at', 
    ];
}
