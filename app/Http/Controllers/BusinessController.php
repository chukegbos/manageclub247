<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Setting;
use App\User;
use App\Ledger;
use App\AccountType;
use App\Account;
use Carbon\Carbon;

class BusinessController extends Controller
{
    public function index()
    {
      if (Auth::guard('web')->check()) {
        return redirect('/home');
      }

      $ledger_id = $this->ledgerID();
      $accounts = Ledger::where('deleted_at', NULL)->groupBy('account')->get();

      /*foreach ($accounts as $account) {
            $each_account = $account->getOriginal('account');

            $get_acc = Ledger::where('deleted_at', NULL)
                ->where('ledger_date', carbon::today())
                ->where('account', $each_account)
                ->where('position', 0)
                ->first();

            if (!$get_acc) {
                $main_account = Account::where('deleted_at', NULL)->find($each_account);
                $account_type = AccountType::where('deleted_at', NULL)->find($main_account->type);

                $debit = Ledger::where('deleted_at', NULL)
                    ->where('ledger_date', '<', carbon::today())
                    ->where('account', $each_account)
                    ->sum('debit');

                $credit = Ledger::where('deleted_at', NULL)
                    ->where('ledger_date', '<', carbon::today())
                    ->where('account', $each_account)
                    ->sum('credit');

                if (($account_type) && ($account_type->type=='Debit')) {
                  $bforward = $debit - $credit;
                  
                  Ledger::create([
                    'ledger_id' => $ledger_id,
                    'ledger_date' => carbon::today(),
                    'account' => $each_account,
                    'amount' => $bforward,
                    'debit' => 0,
                    'credit' => $bforward,
                    'description' => 'Balance brought forward from '.Carbon::today()->subDay()->toFormattedDateString(),
                    'position' => 0,
                  ]);
                }
                else
                {
                  $bforward = $credit - $debit;
                  Ledger::create([
                      'ledger_id' => $ledger_id,
                      'ledger_date' => carbon::today(),
                      'account' => $each_account,
                      'amount' => $bforward,
                      'debit' => $bforward,
                      'credit' => 0,
                      'description' => 'Balance brought forward from '.Carbon::today()->subDay()->toFormattedDateString(),
                      'position' => 0,
                  ]);
                  
                }
            }   
      }*/
      
      return view('auth.login');
    }

    public function login(Request $request)
    { 
        if (Auth::guard('web')->attempt(['email' => $request->email, 'pin' => $request->pin, 'password' => $request->password])) {
           return redirect('/home');
       	}
       	return redirect()->back()->with('success', 'Email or password or PIN is incorrect');
       	return redirect()->back()->withErrors(['Email or password or PIN is incorrect']);
        //If unsuccessful, then redirect back to the login with the form data
        //return redirect()->back()->withInput($request->only('email', 'remember'));
    }
}
