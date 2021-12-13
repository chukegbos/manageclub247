<?php

namespace App;

use App\User;
use App\AccountType;
use App\Account;
use App\AccountTax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Ledger;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ref_id', 'account', 'currency', 'type', 'balance_total', 'description', 'tax_line', 'method', 'balancing_account', 'type_word'
    ];

    protected $dates = [
        'deleted_at', 'main_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'remember_token', 'deleted_at'
    ];


    
    public function getTaxLineAttribute()
    {
        $tax_line = $this->attributes['tax_line'];
        if ($tax_line == 0) {
            return 'Unassigned';
        }
        else
        {
            $tax = AccountTax::where('deleted_at', NULL)->find($tax_line);
            return $tax->name;
        }
    }

    public function getMethodAttribute()
    {
      $id = $this->attributes['method'];
      if ($id==0) {
        return 'System Generated Account';
      }
      else
      {
        return 'Company Generated Account';
      }   
    }

    public function getBalancingAccountAttribute()
    {
        $id = $this->attributes['balancing_account'];
        if ($id) {
          $account = Account::where('deleted_at')->find($id);
          if ($account) {
            return $account;
          }
          else
          {
            return '***';
          }
        }
        else
        {
          return '---';
        }
        
    }
  }
