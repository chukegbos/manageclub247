<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;
use App\Member;
use Carbon\Carbon;
use App\PaymentProduct;

class Debit extends Model
{
    protected $fillable = [
        'product', 'description', 'amount', 'debit_type', 'member_id', 'start_date', 'grace_period', 'created_by', 'status', 'month', 'year', 'get_member_id', 'date_added', 'date_entered', 'period'
    ];

   
}