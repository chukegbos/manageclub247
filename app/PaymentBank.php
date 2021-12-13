<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class PaymentBank extends Model
{
	use SoftDeletes;
	protected $table = 'system_banks';
    protected $fillable = [
    	'bank_name', 'account_number', 'account_name', 'channel'
    ];

    public function getGetBankNameAttribute()
    {
        $code = $this->attributes['bank_name'];
        $bank = DB::table('banks')->where('code' ,$code)->first();
        return $bank->name;
    }
}
