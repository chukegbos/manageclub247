<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;
use App\Member;
use Carbon\Carbon;
use App\Product;

class PaymentDebit extends Model
{
    protected $table = 'default_esc_payment_debits';

    protected $fillable = [
        'product', 'description', 'amount', 'debit_type', 'member_id', 'start_date', 'grace_period', 'created_by', 'status', 'month', 'year', 'get_member_id', 'date_added', 'date_entered', 'period'
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
            return Product::find($id);
        } 
        else {
            return NULL;
        }
    }

    public function getPeriodAttribute()
    {
        $today = Carbon::today();
        if (($this->attributes['start_date']) && ($today > Carbon::parse($this->attributes['start_date'])->addDays($this->attributes['grace_period']))) {
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
            return Member::find($id);
        } 
        else {
            return NULL;
        }
    }
}