<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Ledger;
use App\Account;
use App\AccountType;
use App\Purchase;
use App\Inventory;
use App\InventoryStore;
use App\Setting;
use App\Debtor;
use App\RoomMovement;
use App\ItemPurchase;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\DebtorHistory;
use DB;
use App\Bank;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];

        $query = Purchase::where('deleted_at', NULL)->latest();


        if ($request->name) {
            $query->where('purchase_id', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['purchases'] =  $query->get();
        }
        else{
            $params['purchases'] =  $query->paginate($request->selected);
        }
        $params['all'] = $query->count();
        return $params;
    }

    public function allindex(Request $request)
    {
        $params = [];

        $query = Purchase::where('deleted_at', NULL)->latest();

        if ($request->start_date) {
                $query->where('purchase_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('purchase_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->name) {
            $query->where('purchase_id', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['purchases'] =  $query->get();
        }
        else{
            $params['purchases'] =  $query->paginate($request->selected);
        }


        $query1 = Purchase::where('deleted_at', NULL);

        if ($request->start_date) {
                $query1->where('purchase_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query1->where('purchase_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->name) {
             $query1->where('purchase_id', 'like', '%' . $request->name . '%');
        }

        $params['all'] = $query1->count();
        return $params;
    }

    public function approve($purchase_id)
    {
        $purchase = Purchase::where('deleted_at', NULL)->where('purchase_id', $purchase_id)->first();
        $purchase->approved_by = auth('api')->user()->id;
        $purchase->status = 1;
        $purchase->update();
        return $purchase;
    }

    public function reject($purchase_id)
    {
        $purchase = Purchase::where('deleted_at', NULL)->where('purchase_id', $purchase_id)->first();
        $purchase->approved_by = auth('api')->user()->id;
        $purchase->status = 2;
        $purchase->update();
        return $purchase;
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

    public function accept($purchase_id)
    {
        $purchase = Purchase::where('deleted_at', NULL)->where('purchase_id', $purchase_id)->first();
        $purchase->accepted_by = auth('api')->user()->id;
        $purchase->status_accept = 1;
        $purchase->update();

        $all_purchases = ItemPurchase::where('deleted_at', NULL)->where('purchase_id', $purchase_id)->get();

        $get_price = array();

        foreach ($all_purchases as $item) {
            $inventory = Inventory::where('deleted_at', NULL)->find($item->product_id);

            if ($inventory) {
                $inventory->cost_price = $item->cost;
                $inventory->update();
            }
            array_push($get_price, $item->amount);

            $inventory = InventoryStore::where('deleted_at', NULL)
                ->where('store_id', auth('api')->user()->store)
                ->where('deleted_at', NULL)
                ->where('inventory_id', $item->product_id)
                ->first();
            $inventory->number = $inventory->number + $item->qty;
            $inventory->update();
        }

        $totalPrice = array_sum($get_price);

        $ledger_id = $this->ledgerID();

        if ($purchase->mop==0) {
            $set_debt = new Debtor();
            $set_debt->trans_id = $purchase_id;
            $set_debt->amount = $totalPrice;
            $set_debt->supplier_id = $purchase->supplier;
            $set_debt->processed_by = auth('api')->user()->id;
            $set_debt->store_id = auth('api')->user()->store;
            $set_debt->status = 0;
            $set_debt->type = 0;
            $set_debt->save();
            
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 34,
                'amount' => $totalPrice,
                'debit' => 0,
                'credit' => $totalPrice,
                'description' => 'Credit purchase of Items with ID '. $purchase_id,
                'position' => 2,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Credit purchase of Items with ID '. $purchase_id,
                'position' => 1,
            ]);        
        }

        else{
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 33,
                'amount' => $totalPrice,
                'debit' => 0,
                'credit' => $totalPrice,
                'description' => 'Cash purhcase of Items with ID '. $purchaseId,
                'position' => 2,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Cash purchase of Items with ID '. $purchaseId,
                'position' => 1,
            ]);
        }
        return 'ok';
    }

    public function store(Request $request)
    {
        $purchaseId = rand(9,999999);
        $get_price = array();
        $get_featured_price = array();
        $setting = Setting::find(1);

        if ($request->mop == 1) {
            $account = Account::find($setting->purchase_cash_account);
        }
        else{
            $account = Account::find($setting->purchase_credit_account);
        }

        if (!$account->balancing_account) {
            return ['error' => "Please go to chart of account and set the corresponding accounts"];
        }
        
        foreach ($request->productItems as $item) {
            $it = new ItemPurchase ();
            $it->purchase_id = $purchaseId;
            $it->product_id = $item['id'];

            $it->qty = $item['quantity'] * $item['number_per_crate'];

            $it->total = $item['amount'];
            $it->cost = $item['amount']/($item['quantity'] * $item['number_per_crate']);
            $it->save();
            
            array_push($get_price, $item['amount']);

            $inventory = Inventory::where('deleted_at', NULL)->find($item['id']);

            if ($inventory) {
                $inventory->price = $item['price'];
                $inventory->update();
            }


            $InventoryStore = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $item['id'])->where('store_id', 1)->first();

            if ($InventoryStore) {
                $InventoryStore->number = $inventory->number + ($item['quantity'] * $item['number_per_crate']);
                $InventoryStore->update();
            }
        }

        $totalPrice = array_sum($get_price);
        $ledger_id = $this->ledgerID();

        $purchases = new Purchase ();
        $purchases->purchase_id = $purchaseId;
        $purchases->store_id = auth('api')->user()->getOriginal('store');
        $purchases->initiated_by = auth('api')->user()->id;
        $purchases->purchase_date = $request->purchase_date;
        $purchases->supplier = $request->supplier;
        $purchases->mop = $request->mop;    
        $purchases->type = 1;   
        $purchases->status = 1;    
        $purchases->total_price = $totalPrice;
        $purchases->save();  

        if ($purchases->mop==0) {
            $set_debt = new Debtor();
            $set_debt->trans_id = $purchaseId;
            $set_debt->amount = $totalPrice;
            $set_debt->supplier_id = $purchases->supplier;
            $set_debt->processed_by = auth('api')->user()->id;
            $set_debt->store_id = auth('api')->user()->store;
            $set_debt->status = 0;
            $set_debt->type = 0;
            $set_debt->save();
            
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'amount' => $totalPrice,
                'debit' => $account->id,
                'credit' => $account->balancing_account->id,
                'trans_id' => $purchaseId,
                'description' => 'Credit purchase of Items with ID '. $purchaseId,
            ]);       
        }
        else{
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'amount' => $totalPrice,
                'debit' => $account->id,
                'credit' => $account->balancing_account->id,
                'trans_id' => $purchaseId,
                'description' => 'Cash purhcase of Items with ID '. $purchaseId,
            ]);
        }
        return $purchaseId;
    }

    public function storenon(Request $request)
    {
        $purchaseId = rand(9,999999);
        $get_price = array();

        foreach ($request->productItems as $item) {
            $it = new ItemPurchase ();
            $it->purchase_id = $purchaseId;
            $it->title = $item['product_name'];
            $it->qty = $item['quantity'];
            $it->total = $item['amount'];
            $it->cost = $item['amount']/$item['quantity'];
            $saved_it = $it->save();
            array_push($get_price, $item['amount']);
        }

        $totalPrice = array_sum($get_price);

        $purchases = new Purchase ();
        $purchases->purchase_id = $purchaseId;
        $purchases->store_id = auth('api')->user()->store;
        $purchases->purchase_date = $request->purchase_date;
        $purchases->supplier = $request->supplier;
        $purchases->status = 1;
        $purchases->status_accept = 1;
        $purchases->initiated_by = auth('api')->user()->id;
        $purchases->approved_by = auth('api')->user()->id;
        $purchases->accepted_by = auth('api')->user()->id;
        $purchases->mop = $request->mop;        
        $purchases->total_price = $totalPrice;
        $purchases->type = 0;
        $purchases->save();

        $ledger_id = $this->ledgerID();
        if ($request->mop==0) {
            $set_debt = new Debtor();
            $set_debt->trans_id = $purchaseId;
            $set_debt->amount = $totalPrice;
            $set_debt->supplier_id = $request->supplier;
            $set_debt->processed_by = auth('api')->user()->id;
            $set_debt->store_id = auth('api')->user()->store;
            $set_debt->status = 0;
            $set_debt->type = 0;
            $set_debt->save();
            
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 34,
                'amount' => $totalPrice,
                'debit' => 0,
                'credit' => $totalPrice,
                'description' => 'Credit purchase of Items with ID '. $purchaseId,
                'position' => 2,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Credit purchase of Items with ID '. $purchaseId,
                'position' => 1,
            ]);
 
            
        }

        else{
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 33,
                'amount' => $totalPrice,
                'debit' => 0,
                'credit' => $totalPrice,
                'description' => 'Cash purhcase of Items with ID '. $purchaseId,
                'position' => 2,
            ]);
            
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Cash purchase of Items with ID '. $purchaseId,
                'position' => 1,
            ]);
 
            
        }
        return $purchaseId;
    }

    public function show($purchaseId){
        $params = [];
        $purchase = Purchase::where('deleted_at', NULL)
            ->where('purchase_id', $purchaseId)
            ->first();

        $params['purchase'] = $purchase;

        if ($purchase) {
            if ($purchase->getOriginal('type')==1) {
                $purchases = ItemPurchase::where('item_purchases.deleted_at', NULL)
                ->where('item_purchases.purchase_id', $purchaseId)
                ->where('inventories.deleted_at', NULL)
                ->join('inventories', 'item_purchases.product_id', '=', 'inventories.id')
                ->orderBy('item_purchases.created_at', 'Desc')
                ->select(
                    'item_purchases.id as id',
                    'item_purchases.product_id as product_id',
                    'item_purchases.purchase_id as purchase_id',
                    'item_purchases.qty as qty',
                    'item_purchases.qty as quantity',
                    'inventories.product_name as product_name',
                    'inventories.cost_price as amount',
                    'inventories.id as productId',
                    'item_purchases.total as total_price',
                    'item_purchases.featured_cost as featured_cost'
                )
                ->get();

                $set_purchases = ItemPurchase::where('item_purchases.deleted_at', NULL)
                ->where('item_purchases.purchase_id', $purchaseId)
                ->where('inventories.deleted_at', NULL)
                ->join('inventories', 'item_purchases.product_id', '=', 'inventories.id')
                ->orderBy('item_purchases.created_at', 'Desc')
                ->select(
                    'item_purchases.product_id as id',
                    'item_purchases.qty as quantity',
                    'item_purchases.purchase_id as purchaseId',
                    'item_purchases.total as amount',
                    'inventories.price as price',
                    'inventories.product_name as product_name'
                )
                ->get();
            }
            else
            {
                $purchases = ItemPurchase::where('item_purchases.deleted_at', NULL)
                ->where('item_purchases.purchase_id', $purchaseId)
                ->orderBy('item_purchases.created_at', 'Desc')
                ->select(
                    'item_purchases.id as id',
                    'item_purchases.purchase_id as purchase_id',
                    'item_purchases.qty as qty',
                    'item_purchases.title as product_name',
                    'item_purchases.total as total_price'
                )
                ->get();
            }

            $total_purchase = array();
            foreach ($purchases as $purchase) {
                array_push($total_purchase, $purchase->total_price);
            }

            
            $params['purchases'] = $purchases;
            $params['set_purchases'] = $set_purchases;

            if ($purchase->getOriginal('mop')==0) {
                $debtor = Debtor::where('deleted_at', NULL)->where('trans_id', $purchase->purchase_id)->first();
                if ($debtor) {
                    $params['histories'] = DebtorHistory::where('deleted_at', NULL)->where('debtor_id', $debtor->id)->get();
                    $total_paid = array();
                    foreach ($params['histories'] as $hist) {
                        array_push($total_paid, $hist->amount_paid);
                    }
                    $params['total_paid'] = array_sum($total_paid);
                }
            }

            $params['total_purchase'] = array_sum($total_purchase);
            
            return $params;
        }
        else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::where('deleted_at', NULL)->find($id);
        $get_price = array();
        $get_featured_price = array();
        $setting = Setting::find(1);

        if ($sale->mop == 1) {
            $account = Account::find($setting->cash_account);
        }
        else{
            $account = Account::find($setting->credit_account);
        }

        if (!$account->balancing_account) {
            return ['error' => "Please go to chart of account and set the corresponding accounts"];
        }

        $purchaseId = $purchase->purchase_id;

        $its = ItemPurchase::where('deleted_at', NULL)->where('purchase_id', $purchase->purchase_id)->get();

        foreach ($its as $item) {
            $get_item = ItemPurchase::find($item->id);
            $inventory = InventoryStore::where('deleted_at', NULL)
                ->where('store_id', auth('api')->user()->store)
                ->where('deleted_at', NULL)
                ->where('inventory_id', $get_item->product_id)
                ->first();
            $inventory->number = $inventory->number - $get_item->qty;
            $done = $inventory->update();

            if ($done) {
                $debtor = Debtor::where('trans_id', $purchaseId)->first();
                if ($debtor) {
                    $debtor->deleted_at = NULL;
                    $debtor->update();
                }
                ItemPurchase::destroy($item->id);
            }
        }

        foreach ($request->productItems as $item) {
            $featured_cost = (($item['amount']/$item['quantity'])* $setting->naira_value)+ $setting->expense_ratio;
            $it = new ItemPurchase ();
            $it->purchase_id = $purchaseId;
            $it->product_id = $item['id'];
            $it->qty = $item['quantity'];
            $it->total = $item['amount'];
            $it->cost = $item['amount']/$item['quantity'];
            $it->featured_cost = $featured_cost;
            $it->save();
            
            array_push($get_price, $item['amount']);
            array_push($get_featured_price, $featured_cost);

            $inventory = Inventory::where('deleted_at', NULL)->find($item['id']);

            if ($inventory) {
                $inventory->cost_price = $featured_cost;
                $inventory->price = $item['price'];
                $inventory->update();
            }
            

            $inventory = InventoryStore::where('deleted_at', NULL)
                ->where('store_id', auth('api')->user()->store)
                ->where('deleted_at', NULL)
                ->where('inventory_id', $item['id'])
                ->first();
            $inventory->number = $inventory->number + $item['quantity'];
            $inventory->update();
        }

        $totalPrice = array_sum($get_price);
        $featuredtotalPrice = array_sum($get_featured_price);

        $purchase->update([
            'purchase_date' => $request->purchase_date,
            'mop' => $request->mop,
            'total_price' => $totalPrice,
            'featured_total_price' => $featuredtotalPrice,
        ]);

        $ledger = Ledger::where('deleted_at', NULL)->where('trans_id', $purchaseId)->first();

        if ($purchase->mop==0) {
            $set_debt = new Debtor();
            $set_debt->trans_id = $purchaseId;
            $set_debt->amount = $featuredtotalPrice;
            $set_debt->supplier_id = $purchase->supplier;
            $set_debt->processed_by = auth('api')->user()->id;
            $set_debt->store_id = auth('api')->user()->store;
            $set_debt->status = 0;
            $set_debt->type = 0;
            $set_debt->save();
            
            $ledger->update([
                'ledger_date' => Carbon::today(),
                'amount' => $featuredtotalPrice,
                'debit' => $account->id,
                'credit' => $account->balancing_account->id,
            ]);       
        }

        else{
            $ledger->update([
                'ledger_date' => Carbon::today(),
                'amount' => $featuredtotalPrice,
                'debit' => $account->id,
                'credit' => $account->balancing_account->id,
            ]);
        }
        return $purchase->purchase_id;
    }

    public function destroy($id)
    {
        $purchase = Purchase::where('deleted_at', NULL)->find($id);
        $product = Inventory::where('deleted_at', NULL)->find($purchase->product);

        if ($product->quantity > $purchase->quantity) {
            $product->quantity = $product->quantity - $purchase->quantity;
            $product->update();
            Purchase::Destroy($id);
            return 'ok';
        }
        else
        {
            return 'You cannot delete this purchase detail because the product has decreased in the Inventory';
        }
        
    }
}
