<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Store;
use App\Setting;
use App\InventoryStore;
use App\Member;
use App\Type;
use App\Product;
use App\ProductType;
use App\PaymentDebit;
use App\PaymentBank;
use App\PaymentPos;
use App\FoodKitchen;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Support\Arr;
use Session;
use App\ServiceItem;
use App\Message;
use App\MessageLog;
use App\Ledger;
use App\Account;
use App\AccountType;
use App\Item;
use App\Debtor;
use App\Sale;
use App\User;
use App\Inventory;
use Carbon\Carbon;

class CartController extends Controller
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

    public function addTocart(Request $request)
    {
        $all_product_id = session()->get('product_id');
        foreach ($request->payload as $trade) {
            $gettrade = json_decode($trade);
            $product = Inventory::find($gettrade->inventory_id); 

            $inventory = DB::table('inventory_store')
                ->where('deleted_at', NULL)
                ->where('inventory_id', $gettrade->inventory_id)
                ->where('store_id', auth('api')->user()->store)
                ->first();

            if ($inventory->number > 1) {
                $oldCart = Session::has('cart') ? Session::get('cart') : null;
                $cart = new Cart($oldCart);
                $cart->add($product, $product->id);
                $request->session()->put('cart', $cart);
                $request->session()->push('product_id', $gettrade->inventory_id);
            }
        }
        return $request;
    }

    public function addcart(Request $request, $id)
    {
        $product = Inventory::find($id); 
          
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->add($product, $product->id);
        $request->session()->put('cart', $cart);
        
        $request->session()->push('product_id', $id);

        Session::put('fridge_id', $request->fridge_id);
        return $product;
    }

    public function getCart()
    {
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);

        $params = [];
        
        foreach ($cart->items as $value) {
            $product = DB::table('inventory_store')
                ->where('deleted_at', NULL)
                ->where('store_id', auth('api')->user()->store)
                ->where('inventory_id', $value['product_id'])
                ->first();

            if ($product->number < $value['quantity']) {
                $diff = $value['quantity'] - $product->number;
                for ($i=0; $i < $diff; $i++) { 
                    $oldCart = Session::has('cart') ? Session::get('cart') : null;
                    $cart = new Cart($oldCart);
                    $cart->reduceByOne($value['product_id']);
                  
                    if (count($cart->items) > 0) {
                        Session::put('cart', $cart);
                    }
                    else
                    {
                        Session::forget('cart');
                    }
                }
            }
        }
        $params['products'] = $cart->items;
        $params['inventories'] = DB::table('inventory_store')
            ->where('inventory_store.deleted_at', NULL)
            ->where('inventory_store.store_id', auth('api')->user()->store)
            ->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->get();
            
        $params['totalPrice'] = $cart->totalPrice;
        return $params;
    }

    public function testqty(Request $request)
    {
        $qty = $request->quantity;
        $product_id = $request->product_id;

        $inventory = DB::table('inventory_store')
            ->where('deleted_at', NULL)
            ->where('inventory_id', $product_id)
            ->where('store_id', auth('api')->user()->store)
            ->first();
        
        if (($inventory->number) >= ($qty+1)) {
            return 'ok';
        }
        else
        {
            return 'no';
        }
    }

    public function setqty(Request $request)
    {
        $quantity = $request->quantity;
        $qty = $request->qty;
        $product_id = $request->product_id;

        $inventory = DB::table('inventory_store')
            ->where('deleted_at', NULL)
            ->where('inventory_id', $product_id)
            ->where('store_id', auth('api')->user()->store)
            ->first();
        
        if (($inventory->number) >= ($quantity+1)) {
            for ($i=0; $i < ($quantity - $qty); $i++) { 
                $oldCart = Session::has('cart') ? Session::get('cart') : null;
                $cart = new Cart($oldCart);
                $cart->addByOne($product_id);
              
                if (count($cart->items) > 0) {
                    Session::put('cart', $cart);
                }
                else
                {
                    Session::forget('cart');
                }
            }
        }
        else
        {
            return 'no';
        }
    }

    public function addOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->addByOne($id);
      
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        }
        else
        {
            Session::forget('cart');
        }
        return 'ok';
    }

    public function reduceOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->reduceByOne($id);
      
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        }
        else
        {
            Session::forget('cart');
        }

        return 'ok';
    }

    public function reduceAll($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);

        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        }
        else
        {
            Session::forget('cart');
        }

        return 'ok';
    }

    private function prep_number($phone)
    {
        if(substr($phone, 0, 3) == '234') return $phone;

        $first_char = substr(trim($phone), 0, 1);

        if($first_char == '0' || $first_char == '+')
        {
            return '234' . substr($phone, 1);
        }
        else
        {
            return $phone;
        }
    }

    public function checkout(Request $request)
    {
        $this->validate($request, [
            //'user_id' => 'required',
        ]);
        
        $trans_id = 'CIL'.rand(2,99889997);
        $user = auth('api')->user();       

        $sale = Sale::create([
            'sale_id' => $trans_id,
            'totalPrice' => $request->amount,
            'market_id' => $user->id,
            'store_id' => $user->getOriginal('store'),
            'main_date' => Carbon::now()->addHour(),
            'buyer' => $request->user_id,
            'user_type' => $request->user_type,
            'guest' => $request->guest,
            'mop' => $request->mop,
            'approve' => 0,
            'status' => 'pending'
        ]);
        
        foreach ($request->productItems as $item) {
            $sold = Item::create([
                'code' => $trans_id,
                'product_id' => $item['id'],
                'product_name' => $item['product_name'],
                'totalPrice' => ($item['qty'] * $item['price'] ) - (($item['discount']/100) * $item['price']) ,
                'price' => $item['price'],
                'discount' => $item['discount'],
                'qty' => $item['qty'],
            ]);
        }

        foreach ($request->serviceItems as $item) {
            $kitchen = FoodKitchen::find($item['kitchen']);
           
            $sold = ServiceItem::create([
                'code' => $trans_id,
                'qty' => $item['qty'],
                'amount' => $item['amount'],
                'food' => $item['food_id'],
                'kitchen' => $item['kitchen'],
                'main_kitchen' => $kitchen->kitchen_id,
            ]);
        }

        return $trans_id ;
    }

    public function updateCheckout(Request $request, $id)
    {
        $this->validate($request, [
            //'user_id' => 'required',
        ]);
    
        $user = auth('api')->user();
        $sale = Sale::where('deleted_at', NULL)->find($id);
      
        $sale->update([
            'totalPrice' => $request->amount,
            'market_id' => $user->id,
            'store_id' => auth('api')->user()->getOriginal('store'),
            'main_date' => Carbon::now()->addHour(),
            'buyer' => $request->user_id,
            'mop' => $request->mop,
            'approve' => 0,
            'user_type' => $request->user_type,
            'guest' => $request->guest,
            'status' => 'pending'
        ]);

        $items = Item::where('deleted_at', NULL)->where('code', $sale->sale_id)->get();
        foreach ($items as $item) {
            Item::destroy($item->id);
        }
        
        foreach ($request->productItems as $item) {
            $sold = Item::create([
                'code' => $sale->sale_id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'totalPrice' => ($item['qty'] * $item['price'] ) - (($item['discount']/100) * ($item['qty'] * $item['price'])) ,
                'price' => $item['price'],
                'discount' => $item['discount'],
                'qty' => $item['qty'],
            ]);
        }

        $service_items = ServiceItem::where('deleted_at', NULL)->where('code', $sale->sale_id)->get();
        foreach ($service_items as $item) {
            ServiceItem::destroy($item->id);
        }
        foreach ($request->serviceItems as $item) {
            $sold = ServiceItem::create([
                'code' => $sale->sale_id,
                'title' => $item['title'],
                'price' => $item['price'],
            ]);
        }

        return $sale->sale_id;
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

    public function closedeal(Request $request)
    {
        $setting = Setting::find(1);
        $user = auth('api')->user();
        $sale = Sale::where('sale_id', $request->sale_id)->where('deleted_at', NULL)->first();

        /*if ($sale->mop == 1) {
            $account = Account::find($setting->cash_account);
        }
        else{
            $account = Account::find($setting->credit_account);
        }

        if (!$account->balancing_account) {
            return ['error' => "Please go to chart of account and set the corresponding accounts"];
        }*/

        $items = Item::where('deleted_at', NULL)->where('code', $request->sale_id)->get();
        
        //$ledger_id = $this->ledgerID();
        $buyer_id = $sale->buyer;
        $member = Member::find($buyer_id);
        $buyer = User::where('unique_id', $member->membership_id)->first();
        $the_member = $buyer;

        if ($sale->mop == 1) {
            if ($request->channel==7){
                if ($buyer->bar_wallet >= $sale->totalPrice) {
                    $buyer->bar_wallet = $buyer->bar_wallet - $sale->totalPrice;
                    $buyer->update();
                }
                else
                {
                    $error = 'This member ('. $buyer->name.') do not have enough money in his bar wallet account.';
                    return ['error' => $error];
                }
            }
        }
        else{
            $msg = Message::create([
                'user_id' => auth('api')->user()->id,
                'sender_name' => 'ESPORTSCLUB',
                'message' => 'Bar Payment Debit Notice',
                'page_count' => 1,
            ]);

            $message = 'Memb ID: '. $the_member->unique_id . '; Debit for unpaid drinks; Order ID: ' . $sale->sale_id . '; Amount: N'. $sale->totalPrice . '; Amount Paid: N'. $request->part_payment. '; Amount Indebted: N' . ($sale->totalPrice-$request->part_payment);

            $payment_debit = PaymentDebit::create([
                'description' => $message,
                'member_id' => $member->id,
                'amount' => $sale->totalPrice-$request->part_payment,
                'grace_period' => $request['grace_period'],
                'debit_type' => 0,
                'start_date' => Carbon::today(),
                'date_entered' => Carbon::today(),
                'created_by' => auth('api')->user()->id,
            ]);
            if($payment_debit){
                if($member->phone_1 || $member->phone_2 || $the_member->phone){
                    if ($the_member->phone) {
                        $phone = $the_member->phone;
                    }
                    elseif ($member->phone_1) {
                        $phone = $member->phone_1;
                    }
                    else {
                        $phone = $member->phone_2;
                    }

                    $data1 = [
                        'from' => 'ESPORTSCLUB',
                        'to' => $this->prep_number($phone),
                        'text' => $message,
                    ];

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.silversands.com.ng/sms/1/text/single",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30000,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => json_encode($data1),
                        CURLOPT_HTTPHEADER => array(
                            // Set here requred headers
                            "accept: */*",
                            "accept-language: en-US,en;q=0.8",
                            "content-type: application/json",
                            "Authorization: Basic ZW51Z3VzcG9ydHM6Q3VARW51MjAyMQ==",
                        ),
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);

                    MessageLog::create([
                        'message_id' => $msg->id,
                        'member_id' => $the_member->unique_id,
                        'phone' => $phone,
                    ]);
                }
            }
            /*$set_debt = new Debtor();
            $set_debt->trans_id = $request->sale_id;
            $set_debt->amount = $sale->totalPrice;
            $set_debt->user_id = $buyer_id;
            $set_debt->processed_by = auth('api')->user()->id;
            $set_debt->store_id = auth('api')->user()->store;
            $set_debt->repayment_date = $request->date;
            $set_debt->status = 0;
            $set_debt->type = 1;
            $set_debt->save();

            $account = Account::find($setting->credit_account);
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::now(),
                'user_id' => $user->id,
                'outlet_id' => $sale->getOriginal('store_id'),
                'amount' => $sale->totalPrice,
                'debit' => $account->id,
                'credit' => $account->balancing_account->id,
                'trans_id' => $request->sale_id,
                'description' => 'Sales of product with sale ID '.$request->sale_id,
            ]);*/
        }

        foreach ($items as $item) {
            $product = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $item->product_id)->where('store_id', $user->getOriginal('store'))->first();
            $product->number = $product->number - $item->qty;
            $product->update();
        }

        $sale->market_id = $request->steward_id;
        $sale->cashier_id = $user->id;
        $sale->channel = $request->channel;
        $sale->status = 'concluded';
        $sale->amount_paid = $request->part_payment;
        $sale->link = $request->link;
        $sale->draft_id = $request->draft_id;
        $sale->update();

        return 'ok';
    }

    public function approvequote(Request $request)
    {
        $user = auth('api')->user();
        $sale = Sale::find($request->id);

        $sale->approved = 1;
        $sale->user_id = $user->id;
        $sale->update();
        return $sale;
    }

    public function cancel(Request $request)
    {
        $sale_id = $request->sale_id;

        $sale = Sale::where('deleted_at', NULL)->find($request->id);
        $sale->status = 'returned';
        $sale->update();

        $customer = User::where('deleted_at', NULL)->find($sale->buyer);
        if ($sale->mop==1) {
            $customer->wallet_balance = $customer->wallet_balance + $sale->totalPrice;

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::now(),
                'account' => 33,
                'amount' =>  $sale->totalPrice,
                'debit' => $sale->totalPrice,
                'credit' => 0,
                'user_id' => $user->id,
                'store_id' => $user->store,
                'description' => 'Customer ('.$user->name.') Account Balance Funding',
                'position' => 1,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::now(),
                'account' => 36,
                'amount' =>  $sale->totalPrice,
                'debit' => 0,
                'credit' =>  $sale->totalPrice,
                'user_id' => $user->id,
                'store_id' => $user->store,
                'description' => 'Customer ('.$user->name.') Account Balance Funding',
                'position' => 2,
            ]);
        }

        $customer->update();

        $items = Item::where('deleted_at', NULL)->where('code', $sale->sale_id)->get();
        foreach ($items as $item) {
            $inventory = Inventory::where('deleted_at', NULL)->find($item->product_id);
            if ($inventory) {
                $inventory->quantity = $inventory->quantity + $item->qty;
                $inventory->update();
            }
        }
        return 'ok';
        
    }
}
