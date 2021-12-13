<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;

class AccountType extends Model
{
    use SoftDeletes;
    protected $table = 'account_types';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type', 'cal'
    ];


    protected $dates = [
        'deleted_at', 
    ];
}
