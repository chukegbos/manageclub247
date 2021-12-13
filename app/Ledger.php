<?php

namespace App;

use App\User;
use App\AccountType;
use App\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Ledger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ledger_id', 'account', 'debit', 'credit', 'ledger_date', 'description', 'user_id', 'outlet_id', 'position', 'amount', 'type', 'trans_id'
    ];

    protected $dates = [
        'deleted_at', 'main_date', 
    ];

    protected $hidden = [
      'remember_token', 'deleted_at'
    ];

    public function getDebitAttribute()
    {
        $account_id = $this->attributes['debit'];
       
        $account = Account::where('deleted_at', NULL)->find($account_id);
        if ($account) {
          return $account->account;
        }
        return '--';
    }

    public function getCreditAttribute()
    {
        $account_id = $this->attributes['credit'];
       
        $account = Account::where('deleted_at', NULL)->find($account_id);
        if ($account) {
          return $account->account;
        }
        return '--';
    }
  }
