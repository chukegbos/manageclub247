<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Supplier;
use App\Debtor;
use App\DebtorHistory;
use App\Ledger;
use App\Account;
use App\AccountType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];

        $query = Supplier::where('deleted_at', NULL);

        if($request->keyword)
        {
            $query->where('supplier_name', 'like', $request->keyword)->orWhere('email', 'like', '%' .$request->keyword. '%')->orWhere('phone', 'like', '%' .$request->keyword. '%')->orWhere('contact_person', 'like', '%' .$request->keyword. '%');
        }

        if ($request->selected==0) {
            $params['suppliers'] =  $query->get();
        }
        else{
            $params['suppliers'] =  $query->paginate($request->selected);
        }


        $query1 = Supplier::where('deleted_at', NULL);

        if($request->keyword)
        {
            $query1->where('supplier_name', 'like', $request->keyword)->orWhere('email', 'like', '%' .$request->keyword. '%')->orWhere('phone', 'like', '%' .$request->keyword. '%')->orWhere('contact_person', 'like', '%' .$request->keyword. '%');
        }

        $params['all'] = $query1->count();
        return $params;
    }

    public function allSuppliers(Request $request)
    {
        $search_term = $request[0];
        $query = Supplier::where('deleted_at', NULL);
        if($search_term){
          $query->where('supplier_name', 'like', '%' . $search_term . '%');  
        }
        return $query->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'supplier_name' => 'required|string|max:255',
            'email' => 'required|string|max:191',
            'phone' => 'required|string|max:18',
            'contact_person' => 'required|string|max:180',
            'address' => 'required|string|max:255',
        ]);

        return Supplier::create([
            'supplier_name' => $request['supplier_name'],
            'contact_person' => $request['contact_person'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'bank_name' => $request['bank_name'],
            'bank_account' => $request['bank_account'],
            'account_name' => $request['account_name'],
        ]);
    }

    public function show($code)
    {
        $params = [];
        $params['bar'] = Bar::where('deleted_at', NULL)->where('code', $code)->first();

        if ($params['bar']) {
            $params['fridges'] = Fridge::where('deleted_at', NULL)->where('bar_code', $code)->paginate(10);
            return $params;
        }
        else{
            return response()->json(['error' => 'Such bar does not exist'], 500);
        }
    }

    public function singleSupplier($id){
        $supplier = Supplier::find($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::where('deleted_at')->find($id);

        $this->validate($request, [
            'supplier_name' => 'required|string|max:255',
            'email' => 'required|string|max:191',
            'phone' => 'required|string|max:18',
            'contact_person' => 'required|string|max:180',
            'address' => 'required|string|max:255',
        ]);

        $supplier->update([
            'supplier_name' => $request['supplier_name'],
            'contact_person' => $request['contact_person'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'bank_name' => $request['bank_name'],
            'bank_account' => $request['bank_account'],
            'account_name' => $request['account_name'],
        ]);
        return ['message' => "Success"];
    }

    public function destroy(Request $request)
    {
       foreach ($request->selected as $id) {
            Supplier::Destroy($id);
        }
        return 'ok';
    }

    public function debtors(Request $request)
    {
        $params = [];
        
        $query = Debtor::where('debtors.deleted_at', NULL)->where('debtors.type', 0)->orderBy('debtors.created_at', 'desc');

        if ($request->keyword) {
            $query->where('debtors.trans_id', $request->keyword);
        }

        if ($request->supplier) {
            $query->where('debtors.supplier_id', $request->supplier);
        }

        if ($request->status) {
            $query->where('debtors.status', $request->status);
        }

        if ($request->selected==0) {
            $params['report_data'] =  $query->get();
        }
        else{
            $params['report_data'] =  $query->paginate($request->selected);
        }
        

        $query1 = Debtor::where('debtors.deleted_at', NULL)->where('debtors.type', 0);

        if ($request->keyword) {
            $query1->where('debtors.trans_id', $request->keyword);
        }

        if ($request->supplier) {
            $query1->where('debtors.supplier_id', $request->supplier);
        }

        if ($request->status) {
            $query1->where('debtors.status', $request->status);
        }
        $params['all'] = $query1->count();

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

    public function storedebtors(Request $request)
    {
        $debtor = Debtor::where('deleted_at', NULL)->where('trans_id', $request->trans_id)->first();
        if ($debtor) {
            $debt = new DebtorHistory();
            $debt->debtor_id = $debtor->id;
            $debt->amount_paid = $request->amount_paid;
            $debt->processed_by = auth('api')->user()->id;
            $debt->store_id = auth('api')->user()->store;
            $debt->purchase_date =  ($request->purchase_date) ? $request->purchase_date : Carbon::today();
            $debt->save();
 
            $debtor->amount_paid = $debtor->amount_paid + $request->amount_paid;
            $debtor->update();

            if ($debtor->amount == $debtor->amount_paid) {
                $debtor->status = 1;
                $debtor->update();
            }

            if ($request->amount_paid == $request->amount) {
                $message = 'Complete payment of purchase of Items with ID '. $debtor->trans_id;
            }
            else{
                $message = 'Part payment of purchase of Items with ID '. $debtor->trans_id;
            }

            $ledger_id = $this->ledgerID();
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::now(),
                'account' => 34,
                'amount' => $request->amount_paid,
                'debit' => $request->amount_paid,
                'credit' => 0,
                'description' => $message,
                'position' => 1,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::now(),
                'account' => 33,
                'amount' => $request->amount_paid,
                'debit' => 0,
                'credit' => $request->amount_paid,
                'description' => $message,
                'position' => 2,
            ]);
        }
        return $debtor;
    }
}
