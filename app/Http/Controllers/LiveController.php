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

class LiveController extends Controller
{

    public function members(Request $request)
    {
        set_time_limit(0);

        $query = User::where('users.deleted_at', NULL)
            ->where('default_esc_members.deleted_at', NULL)
            ->where('users.role', 0)
            ->join('default_esc_members', 'users.unique_id', '=', 'default_esc_members.membership_id')
            ->join('default_esc_member_card_numbers', 'default_esc_members.id', '=', 'default_esc_member_card_numbers.member_id');

        $users = $query->select(
            'users.id as id',
            'users.unique_id as unique_id',
            'users.name as name',
            'users.email as email',
            'users.access as access',
            'users.phone as phone',
            'users.approved as approved',
            'users.door_access as door_access',
            'users.card_numbers as card_numbers'
        )
        
        ->orderBy('users.name', 'desc')
        ->get();

        return $users;
    }

    public function ledgerID()
    {
        $last_ledger = Ledger::where('deleted_at', null)->latest()->first();
        if (!$last_ledger) {
            $ledger_id = 100000;
        }
        else{
           $ledger_id = $last_ledger->ledger_id + 100; 
        }

        return $ledger_id;
    }

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

    public function dent()
    {
        //return 'ok';
        $dent = Setting::where('dent', 1)->first();
        if(isset($dent)){
            $id = $dent->id;
            if($id==1){
                $dent->id=2;
                $dent->update();
            }
            else{
                $dent->id=1;
                $dent->update();
            }
        }
        
        return redirect('/');
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
