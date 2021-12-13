<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;
use Carbon\Carbon;
use App\PaymentProduct;

class PaymentDebit extends Model
{
    protected $table = 'default_esc_payment_debits';

    protected $fillable = [
        'product', 'description', 'amount', 'debit_type', 'member_id', 'start_date', 'grace_period', 'created_by', 'status', 'month', 'year', 'get_member_id', 'date_added', 'date_entered'
    ];

    public function getCreatedByAttribute()
    {
        $id = $this->attributes['created_by'];
        if ($id) {
            return User::find($id);
        } 
        else {
            return NULL;
        }
    }

    public function getProductAttribute()
    {
        $id = $this->attributes['product'];
        if ($id) {
            return PaymentProduct::where('deleted_at', NULL)->find($id);
        } 
        else {
            return NULL;
        }
    }

    public function getPeriodAttribute()
    {

        $start_date = $this->attributes['start_date'];
        $grace_period = $this->attributes['grace_period'];
        $today = Carbon::today();
        if ($today > Carbon::parse($start_date)->addDays($grace_period)) {
            return 0;
        }
        else {
            return 1;
        }
    }

    public function getGetMemberIdAttribute()
    {
        $id = $this->attributes['member_id'];
        if ($id) {
            return User::find($id);
        } 
        else {
            return NULL;
        }
    }
}