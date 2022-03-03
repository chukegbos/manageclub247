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
        'product', 'description', 'amount', 'debit_type', 'member_id', 'start_date', 'grace_period', 'created_by', 'status', 'month', 'year', 'get_member_id', 'date_added', 'date_entered', 'period', 'door_access'
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
            $product = Product::find($id);
            if ($product) {
                return $product;
            }
            else {
                return NULL;
            }
        } 
        else {
            return NULL;
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