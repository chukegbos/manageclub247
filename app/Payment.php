<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;
use App\PaymentDebit;
use App\PaymentChannel;

class Payment extends Model
{
    protected $table = 'default_esc_payments';

    protected $fillable = [
        'debit_id', 'member_id', 'amount', 'payment_channel', 'created_by', 'receipt_number', 'pos', 'bank', 'get_product', 'get_member', 'rec_id'
    ];

    /*public function getRecIdAttribute()
    {
        $debit_id = $this->attributes['debit_id'];
        $member_id = $this->attributes['member_id'];
        return 'ESC'.$debit_id.$member_id;
    }*/

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


    public function getCreatedByAttribute()
    {
        $id = $this->attributes['created_by'];
        if ($id) {
            $user = User::where('deleted_at', NULL)->find($id);
            if ($user) {
                return $user->name;
            }
            else {
                return NULL;
            }
        } 
        else {
            return NULL;
        }
    }

    public function getPaymentChannelAttribute()
    {
        $id = $this->attributes['payment_channel'];
        if ($id) {
            $channel = PaymentChannel::find($id);
            if ($channel) {
                return $channel->title;
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