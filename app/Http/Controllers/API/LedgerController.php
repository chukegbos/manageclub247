<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Ledger;
use App\Setting;
use App\Account;
use App\AccountType;
use Illuminate\Support\Str;
use DB;

class LedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }


    public function index(Request $request)
    {
        $params = [];

        $query =  Ledger::where('deleted_at', NULL)->orderBy('id', 'desc');
        /*if($request->account)
        {
            $query->where('account', $request->account);
        }*/

        if ($request->start_date) {
            $query->where('ledger_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('ledger_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->selected==0) {
            $params['ledgers'] =  $query->get();
        }
        else{
            $params['ledgers']  =  $query->paginate($request->selected);
        }


        $query1 =  Ledger::where('deleted_at', NULL);
        /*if($request->account)
        {
            $query->where('account', $request->account);
        }*/

        if ($request->start_date) {
            $query1->where('ledger_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query1->where('ledger_date', '<=', $request->end_date . ' 23:59');
        }

        $params['all'] = $query1->count();




        //Ledger
        $params['grouped_ledgers'] = Ledger::where('deleted_at', NULL)->groupBY('account')->get();
        
        $params['all_accounts'] = array();

        foreach ($params['grouped_ledgers'] as $ledger) {
            $each_account = $ledger->getOriginal('account');

            $get_acc = Ledger::where('deleted_at', NULL)->where('account', $each_account);

            if ($request->start_date) {
                $get_acc->where('ledger_date', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $get_acc->where('ledger_date', '<=', $request->end_date . ' 23:59');
            }

            if ($request->selected==0) {
                $get_accounts = $get_acc->get();
            }
            else{
                $get_accounts =  $get_acc->paginate($request->selected);
            }

            array_push($params['all_accounts'], $get_accounts);
        }
        return $params;
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

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
        ]);

        $ledger_id = $this->ledgerID();

        Ledger::create([
            'ledger_id' => $ledger_id,
            'ledger_date' => $request->ledger_date,
            'account' => 30,
            'amount' => $request->amount,
            'debit' => 0,
            'credit' => $request->amount,
            'description' => $request->description,
            'position' => 2,
        ]);

        Ledger::create([
            'ledger_id' => $ledger_id,
            'ledger_date' => $request->ledger_date,
            'account' => 33,
            'amount' => $request->amount,
            'debit' => $request->amount,
            'credit' => 0,
            'description' => $request->description,
            'position' => 1,
        ]);

        
        return 'ok';
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'account' => 'required',
        ]);

        $account = Account::where('deleted_at', NULL)->find($id);

        $account->update([
            'account' => $request->account,
            'currency' => $request->currency,
            'type' => ($request->type) ? $request->type : 0,
            'description' => $request->description,
            'tax_line' => ($request->tax_line) ? $request->tax_line : 0,
        ]);

        return 'ok';
    }

    public function destroy($id)
    {
        $account = Account::where('deleted_at', NULL)->find($id);
        $account->delete();
    }
}
