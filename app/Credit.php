<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Credit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ref_id', 'prev_amount', 'amount', 'user_id', 'customer_id'
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $hidden = [
      'remember_token', 'deleted_at'
    ];


    public function getUserIdAttribute()
    {
        $user_id = $this->attributes['user_id'];
        if ($user_id) {
          $user = User::where('deleted_at', NULL)->find($user_id);
          return $user->name;
        }
        else
        {
            return '---';
        }
    }

    public function getBalanceTotalAttribute()
    {
      $id = $this->attributes['id'];
      $credit = ledger::where('deleted_at', NULL)->where('account', $id)->sum('credit');
      $debit = ledger::where('deleted_at', NULL)->where('account', $id)->sum('debit');
      return( $credit - $debit);
    }
  }
