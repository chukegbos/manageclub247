<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\IncomeCategory;
use App\Account;
use App\AccountType;
use App\Ledger;
use Illuminate\Support\Str;
use DB;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }


    public function index(Request $request)
    {
        $params = [];
        $query =  Account::where('accounts.deleted_at', NULL)
            ->join('account_types', 'accounts.type', '=', 'account_types.id');

        if($request->account)
        {
            $query->where('accounts.account', $request->account);
        }

        if($request->ref_id)
        {
            $query->where('accounts.ref_id', $request->ref_id);
        }

        if($request->type)
        {
            $query->where('accounts.type', $request->type);
        }

        if($request->tax_line)
        {
            $query->where('accounts.tax_line', $request->tax_line);
        }
        
        $query->orderBy('accounts.ref_id', 'asc')
        ->select(
            'accounts.id as id',
            'accounts.ref_id as ref_id',
            'accounts.account as account',
            'accounts.currency as currency',
            'accounts.balance_total as balance_total',
            'accounts.balancing_account as balancing_account',
            'accounts.description as description',
            'accounts.method as method',
            'account_types.name as type',
            'account_types.id as type_id'
        );

        if ($request->selected==0) {
            $params['accounts'] =  $query->get();
        }
        else{
            $params['accounts'] =  $query->paginate($request->selected);
        }
        $params['count_all'] =  $query->count();
        return $params;
    }

    public function trialbalance(Request $request)
    {
        $params = [];

        $query = Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.deleted_at', NULL)
            ->groupBy('ledgers.account');

        if ($request->end_date) {
            $query->where('ledgers.ledger_date', '<=', $request->end_date);
        } 

        $params['accounts'] = $query->select(DB::raw("SUM(ledgers.amount) As amount, accounts.account as account_name, accounts.type_word as type"))->get();

        $query_expense1 = Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.deleted_at', NULL)
            ->groupBy('ledgers.account')
            ->where('accounts.type_word', 'DB');

        if ($request->end_date) {
            $query_expense1->where('ledgers.ledger_date', '<=', $request->end_date);
        }

        $expense1 = $query_expense1->sum('amount');

        $query_expense2 = Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.deleted_at', NULL)
            ->groupBy('ledgers.account')
            ->where('accounts.type_word', 'DP');

        if ($request->end_date) {
            $query_expense2->where('ledgers.ledger_date', '<=', $request->end_date);
        }
        $expense2 = $query_expense2->sum('amount');

        $params['total_expense'] = $expense1 + $expense2;




        $query_income1 = Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.deleted_at', NULL)
            ->groupBy('ledgers.account')
            ->where('accounts.type_word', 'CB');

        if ($request->end_date) {
            $query_income1->where('ledgers.ledger_date', '<=', $request->end_date);
        }
        $income1 = $query_income1->sum('amount');

        $query_income2 = Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.deleted_at', NULL)
            ->groupBy('ledgers.account')
            ->Where('accounts.type_word', 'CP');

        if ($request->end_date) {
            $query_income2->where('ledgers.ledger_date', '<=', $request->end_date);
        }
        $income2 = $query_income2->sum('amount');

        $params['total_income'] = $income1 + $income2;
        return $params;
    }

    public function balancesheet(Request $request)
    {
        $params = [];

        $query_income =  Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.deleted_at', NULL)
            ->where('accounts.type_word', 'DB')
            ->groupBy('ledgers.account');

        if ($request->end_date) {
            $query_income->where('ledgers.ledger_date', '<=', $request->end_date);
        } 


        $params['income'] = $query_income->select(DB::raw("SUM(ledgers.amount) As amount, accounts.account as account_name"))->get();
    
        $get_total_income = array();
        foreach ($params['income'] as $account) {
            array_push($get_total_income, $account->amount);
        }
        $params['total_income'] = array_sum($get_total_income);






        //expense
        $query_expense =  Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.type_word', 'CB')
            ->where('accounts.deleted_at', NULL)
            ->groupBy('ledgers.account');

        if ($request->end_date) {
            $query_expense->where('ledgers.ledger_date', '<=', $request->end_date);
        }

        $params['expense'] = $query_expense->select(DB::raw("SUM(ledgers.amount) As amount, accounts.account as account_name"))->get();

        $get_total_expense = array();

        foreach ($params['expense'] as $account) {
            array_push($get_total_expense, $account->amount);
        }

        $params['total_expense'] = array_sum($get_total_expense);
        
        $params['profit'] = $params['total_income'] - $params['total_expense'];
        return $params;    
    }

    public function profitloss(Request $request)
    {
        $params = [];

        $query_income =  Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.deleted_at', NULL)
            ->where('accounts.type_word', 'CP')
            ->groupBy('ledgers.account');

        if ($request->end_date) {
            $query_income->where('ledgers.ledger_date', '<=', $request->end_date);
        } 


        $params['income'] = $query_income->select(DB::raw("SUM(ledgers.amount) As amount, accounts.account as account_name"))->get();
    
        $get_total_income = array();
        foreach ($params['income'] as $account) {
            array_push($get_total_income, $account->amount);
        }
        $params['total_income'] = array_sum($get_total_income);

        //expense
        $query_expense =  Ledger::where('ledgers.deleted_at', NULL)
            ->join('accounts', 'ledgers.account', '=', 'accounts.id')
            ->where('accounts.type_word', 'DP')
            ->where('accounts.deleted_at', NULL)
            ->groupBy('ledgers.account');

        if ($request->end_date) {
            $query_expense->where('ledgers.ledger_date', '<=', $request->end_date);
        }

        $params['expense'] = $query_expense->select(DB::raw("SUM(ledgers.amount) As amount, accounts.account as account_name"))->get();

        $get_total_expense = array();

        foreach ($params['expense'] as $account) {
            array_push($get_total_expense, $account->amount);
        }

        $params['total_expense'] = array_sum($get_total_expense);
        
        $params['profit'] = $params['total_income'] - $params['total_expense'];
        return $params;    
    }

    public function search(Request $request)
    {
        $search_term = $request[0];
        $account = Account::where('deleted_at', NULL)->where('account', 'like', '%' . $search_term . '%')->get();
        return $account;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'account' => 'required',
            'type' => 'required',
            'corresponding_account' => 'required',
        ]);

        $type = AccountType::find($request->type);
        if ($type) {
            $ref_id = $type->id.'00'.rand(1,9999);
        }
        else{
           $ref_id = 'X00'.rand(1,9999); 
        }

        return Account::create([
            'ref_id' => $ref_id,
            'account' => $request->account,
            'type' => $request->type,
            'balancing_account' => $request->corresponding_account,
            'description' => $request->description,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'account' => 'required',
            'type' => 'required',
            /*'corresponding_account' => 'required',*/
        ]);

        $account = Account::where('deleted_at', NULL)->find($id);

        $typed = ($request->type) ? $request->type : $account->type ;

        $type = AccountType::find($request->type);

        if ($type) {
            $characters = ltrim($account->ref_id, $account->ref_id[0]);
            $ref_id = $type->id.$characters;
        }       

        $account->update([
            'ref_id' => $ref_id,
            'account' => $request->account,
            'type' => $typed,
            'balancing_account' => $request->corresponding_account,
        ]);

        return 'ok';
    }

    public function destroy($id)
    {
        $account = Account::where('deleted_at', NULL)->find($id);
        $account->delete();
    }
}
