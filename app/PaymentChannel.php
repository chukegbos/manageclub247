<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;

class PaymentChannel extends Model
{
    protected $table = 'default_esc_payment_channels';

    protected $fillable = [
        'title'
    ];
}