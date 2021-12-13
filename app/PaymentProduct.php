<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;

class PaymentProduct extends Model
{
    use SoftDeletes;
    protected $table = 'default_esc_payment_products';

    protected $fillable = [
        'product_id', 'payment_name', 'amount', 'type', 'category', 'door_access', 'reoccuring_day', 'grace_period', 'created_by'
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
}