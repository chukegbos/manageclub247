<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Ledger;
use App\Account;
use App\MarketItem;
use App\MarketList;
use App\FoodInventory;
use App\Setting;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use App\Bank;
use Carbon\Carbon;

class MarketController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];

        $query = MarketList::where('deleted_at', NULL)->latest();


        if ($request->name) {
            $query->where('market_id', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['markets'] =  $query->get();
        }
        else{
            $params['markets'] =  $query->paginate($request->selected);
        }
        $params['all'] = $query->count();
        return $params;
    }

    public function store(Request $request)
    {
        $marketId = rand(9,999999);
        $get_price = array();
        $get_featured_price = array();
        $setting = Setting::find(1);

        foreach ($request->productItems as $item) {
            $it = new MarketItem ();
            $it->market_id = $marketId;
            $it->item = $item['id'];
            $it->quantity = $item['quantity'];
            $it->amount = $item['amount'];
            $it->save();
            array_push($get_price, $item['amount']);

            $inventory = FoodInventory::find($item['id']);
            $inventory->quantity = $item['quantity'] + $inventory->quantity;
            $inventory->amount = $item['amount'];
            $inventory->update();
        }

        $totalPrice = array_sum($get_price);

        $markets = new MarketList ();
        $markets->market_id = $marketId;
        $markets->user_id = auth('api')->user()->id;
        $markets->purchase_date = $request->purchase_date;
        $markets->amount = $totalPrice;
        $markets->save();  
        return $marketId;
    }

    public function viewstore(Request $request){
        $params = [];
        $qy = MarketItem::where('deleted_at', NULL)->where('status', 1);

        if ($request->selected==0) {
            $params['items'] =  $qy->get();
        }
        else{
            $params['items']  =  $qy->paginate(20);
        }
        return $params;
    }

    public function destroyitem(Request $request)
    {
       foreach ($request->selected as $id) {
            $marketitem = MarketItem::find($id);
            $marketitem->status = 0;
            $marketitem->update(); 
        }
        return 'ok';
    }



    public function allindex(Request $request)
    {
        $params = [];

        $query = market::where('deleted_at', NULL)->latest();

        if ($request->start_date) {
                $query->where('market_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('market_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->name) {
            $query->where('market_id', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['markets'] =  $query->get();
        }
        else{
            $params['markets'] =  $query->paginate($request->selected);
        }


        $query1 = market::where('deleted_at', NULL);

        if ($request->start_date) {
                $query1->where('market_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query1->where('market_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->name) {
             $query1->where('market_id', 'like', '%' . $request->name . '%');
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

    public function accept($market_id)
    {
        $market = market::where('deleted_at', NULL)->where('market_id', $market_id)->first();
        $market->accepted_by = auth('api')->user()->id;
        $market->status_accept = 1;
        $market->update();

        $all_markets = Itemmarket::where('deleted_at', NULL)->where('market_id', $market_id)->get();

        $get_price = array();

        foreach ($all_markets as $item) {
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

        if ($market->mop==0) {
            $set_debt = new Debtor();
            $set_debt->trans_id = $market_id;
            $set_debt->amount = $totalPrice;
            $set_debt->supplier_id = $market->supplier;
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
                'description' => 'Credit market of Items with ID '. $market_id,
                'position' => 2,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Credit market of Items with ID '. $market_id,
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
                'description' => 'Cash purhcase of Items with ID '. $marketId,
                'position' => 2,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Cash market of Items with ID '. $marketId,
                'position' => 1,
            ]);
        }
        return 'ok';
    }

    

    public function storenon(Request $request)
    {
        $marketId = rand(9,999999);
        $get_price = array();

        foreach ($request->productItems as $item) {
            $it = new Itemmarket ();
            $it->market_id = $marketId;
            $it->title = $item['product_name'];
            $it->qty = $item['quantity'];
            $it->total = $item['amount'];
            $it->cost = $item['amount']/$item['quantity'];
            $saved_it = $it->save();
            array_push($get_price, $item['amount']);
        }

        $totalPrice = array_sum($get_price);

        $markets = new market ();
        $markets->market_id = $marketId;
        $markets->store_id = auth('api')->user()->store;
        $markets->market_date = $request->market_date;
        $markets->supplier = $request->supplier;
        $markets->status = 1;
        $markets->status_accept = 1;
        $markets->initiated_by = auth('api')->user()->id;
        $markets->approved_by = auth('api')->user()->id;
        $markets->accepted_by = auth('api')->user()->id;
        $markets->mop = $request->mop;        
        $markets->total_price = $totalPrice;
        $markets->type = 0;
        $markets->save();

        $ledger_id = $this->ledgerID();
        if ($request->mop==0) {
            $set_debt = new Debtor();
            $set_debt->trans_id = $marketId;
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
                'description' => 'Credit market of Items with ID '. $marketId,
                'position' => 2,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Credit market of Items with ID '. $marketId,
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
                'description' => 'Cash purhcase of Items with ID '. $marketId,
                'position' => 2,
            ]);
            
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 20,
                'amount' => $totalPrice,
                'debit' => $totalPrice,
                'credit' => 0,
                'description' => 'Cash market of Items with ID '. $marketId,
                'position' => 1,
            ]);
 
            
        }
        return $marketId;
    }

    public function show($marketId){
        $params = [];
        $marketlist = MarketList::where('deleted_at', NULL)
            ->where('market_id', $marketId)
            ->first();

        $params['marketlist'] = $marketlist;

        if ($marketlist) {
            $items = MarketItem::where('deleted_at', NULL)->where('market_id', $marketId)->latest()->get();

            $total_items = array();
            foreach ($items as $item) {
                array_push($total_items, $item->amount);
            }

            $params['lists'] = $items;
            $params['totalPrice'] = array_sum($total_items);
            
            return $params;
        }
        else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function update(Request $request, $id)
    {
        $market = market::where('deleted_at', NULL)->find($id);
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

        $marketId = $market->market_id;

        $its = Itemmarket::where('deleted_at', NULL)->where('market_id', $market->market_id)->get();

        foreach ($its as $item) {
            $get_item = Itemmarket::find($item->id);
            $inventory = InventoryStore::where('deleted_at', NULL)
                ->where('store_id', auth('api')->user()->store)
                ->where('deleted_at', NULL)
                ->where('inventory_id', $get_item->product_id)
                ->first();
            $inventory->number = $inventory->number - $get_item->qty;
            $done = $inventory->update();

            if ($done) {
                $debtor = Debtor::where('trans_id', $marketId)->first();
                if ($debtor) {
                    $debtor->deleted_at = NULL;
                    $debtor->update();
                }
                Itemmarket::destroy($item->id);
            }
        }

        foreach ($request->productItems as $item) {
            $featured_cost = (($item['amount']/$item['quantity'])* $setting->naira_value)+ $setting->expense_ratio;
            $it = new Itemmarket ();
            $it->market_id = $marketId;
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

        $market->update([
            'market_date' => $request->market_date,
            'mop' => $request->mop,
            'total_price' => $totalPrice,
            'featured_total_price' => $featuredtotalPrice,
        ]);

        $ledger = Ledger::where('deleted_at', NULL)->where('trans_id', $marketId)->first();

        if ($market->mop==0) {
            $set_debt = new Debtor();
            $set_debt->trans_id = $marketId;
            $set_debt->amount = $featuredtotalPrice;
            $set_debt->supplier_id = $market->supplier;
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
        return $market->market_id;
    }

    public function destroy($id)
    {
        $market = market::where('deleted_at', NULL)->find($id);
        $product = Inventory::where('deleted_at', NULL)->find($market->product);

        if ($product->quantity > $market->quantity) {
            $product->quantity = $product->quantity - $market->quantity;
            $product->update();
            market::Destroy($id);
            return 'ok';
        }
        else
        {
            return 'You cannot delete this market detail because the product has decreased in the Inventory';
        }
        
    }
}
