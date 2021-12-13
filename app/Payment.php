<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;
use App\PaymentDebit;

class Payment extends Model
{
    protected $table = 'default_esc_payments';

    protected $fillable = [
        'debit_id', 'member_id', 'amount', 'payment_channel', 'created_by', 'receipt_number', 'pos', 'bank', 'get_product', 'get_member'
    ];

    public function getGetProductAttribute()
    {
        $id = $this->attributes['debit_id'];
        if ($id) {
            $payment = PaymentDebit::where('deleted_at', NULL)->find($id);
            if ($payment) {
                return $payment;
            }
            else {
                return NULL;
            }
        } 
        else {
            return NULL;
        }
    }

    public function getGetMemberAttribute()
    {
        $id = $this->attributes['member_id'];
        if ($id) {
            $user = Member::where('deleted_at', NULL)->find($id);
            if ($user) {
                return $user;
            }
            else {
                return NULL;
            }
        } 
        else {
            return NULL;
        }
    }
}