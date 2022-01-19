<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Member;
use App\Type;
use App\Product;
use App\ProductType;
use App\PaymentDebit;
use App\PaymentBank;
use App\PaymentPos;
use App\Message;
use App\MessageLog;
use App\Payment;
use App\Ledger;
use App\PaymentChannel;
use App\User;
use App\Setting;
use App\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use DB;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $query = Payment::where('member_id', '!=', NULL);

        if ($request->selected==0) {
            $params['payments'] =  $query->get();
        }
        else{
            $params['payments'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();

        return $params;
    }

    public function view($id)
    {
        return Payment::findOrFail($id);
    }

    public function getmember($id)
    {
        $member = Member::find($id);
        return $user = User::where('deleted_at', NULL)->where('unique_id', $member->membership_id)->first();
    }

    public function debit(Request $request)
    {    
        $query = PaymentDebit::where('default_esc_payment_debits.status', 0)
            ->join('default_esc_members', 'default_esc_payment_debits.member_id', '=', 'default_esc_members.id')
            ->orderBy('default_esc_payment_debits.created_at', 'desc');

        if ($request->name) {
            $query->where('default_esc_payment_debits.description', 'like', '%' . $request->name . '%')
                ->orWhere('default_esc_members.first_name', 'like', '%' . $request->name . '%')
                ->orWhere('default_esc_members.last_name', 'like', '%' . $request->name . '%')
                ->orWhere('default_esc_members.middle_name', 'like', '%' . $request->name . '%')
                ->orWhere('default_esc_members.membership_id', 'like', '%' . $request->name . '%');
        }

        $query->select(
            'default_esc_members.first_name as first_name',
            'default_esc_members.last_name as last_name',
            'default_esc_payment_debits.period as period',
            'default_esc_payment_debits.amount as amount',
            'default_esc_payment_debits.member_id as member_id',
            'default_esc_payment_debits.product as product',
            'default_esc_payment_debits.description as description',
            'default_esc_payment_debits.start_date as start_date',
            'default_esc_payment_debits.grace_period as grace_period',
            'default_esc_payment_debits.id as id'
        );

        if ($request->selected==0) {
            $params['debits'] =  $query->get();
        }
        else{
            $params['debits'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();
        $params['products'] = Product::where('deleted_at', NULL)->get();
        $params['members'] = Member::where('deleted_at', NULL)->get();
        $params['mop'] = PaymentChannel::get();

        return $params;
    }

    public function storedebit(Request $request)
    {
        $this->validate($request, [
            'member' => 'required',
            'product' => 'required',
            'amount' => 'required',
        ]);
        $product = Product::find($request['product']);

        $payment_debit = PaymentDebit::create([
            'product' => $request['product'],
            'description' => $product->payment_name,
            'member_id' => $request['member'],
            'amount' => $request['amount'],
            'grace_period' => $request['grace_period'],
            'debit_type' => 0,
            'member_id' => $request['member'],
            'start_date' => Carbon::today(),
            'date_entered' => Carbon::today(),
            'created_by' => auth('api')->user()->id,
        ]);

        $member = Member::findOrFail($request['member']);
        $the_member = User::where('unique_id', $member->membership_id)->first();

        if (($the_member) && ($the_member->wallet_balance >= $request->amount)) {  
            Payment::create([
                'debit_id' => $payment_debit->id,
                'member_id' => $payment_debit->member_id,
                'amount' => $request->amount,
                'payment_channel' => 6,
                'created_by' => auth('api')->user()->id,
            ]);

            $payment_debit->status = 1;
            $payment_debit->update();

            $the_member->wallet_balance = $the_member->wallet_balance - $request->amount;
            $the_member->update();
        }
        else {
            $msg = Message::create([
                'user_id' => auth('api')->user()->id,
                'sender_name' => 'ESPORTSCLUB',
                'message' => 'Individual Debit Message',
                'page_count' => 1,
            ]);

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

                $message = 'Membership ID: '.$the_member->unique_id.'; Debit for '.$request->description.'; Amount: N'.$request->amount;

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
        return ['Message' => 'Created'];
    }

    public function updatedebit(Request $request, $id)
    {
        $debit = PaymentDebit::findOrFail($id);
        $this->validate($request, [
            'amount' => 'required',
        ]);

        $debit->update([
            'amount' => $request['amount'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function destroydebit(Request $request)
    {
        foreach ($request->selected as $id) {
            PaymentDebit::Destroy($id);
        }
        return 'ok';  
    }

    public function pos(Request $request)
    {
        $query = PaymentPos::where('deleted_at', NULL);

        if ($request->name) {
            $query->where('code', 'like', '%' . $request->name . '%')->orWhere('name', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['pos'] =  $query->get();
        }
        else{
            $params['pos'] =  $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();

        return $params;
    }

    public function storepos(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
            'name' => 'required',
        ]);
        
        $pos = PaymentPos::where('deleted_at', NULL)->where('code', $request['code'])->first();
        if ($pos) {
            return ['Error' => 'POS Already Exist'];
        }
        PaymentPos::create([
            'code' => $request['code'],
            'name' => $request['name'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function updatepos(Request $request, $id)
    {
        $pos = PaymentPos::findOrFail($id);
        $this->validate($request, [
            'code' => 'required',
            'name' => 'required',
        ]);

        $pos->update([
            'code' => $request['code'],
            'name' => $request['name'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function destroypos(Request $request)
    {
        foreach ($request->selected as $id) {
            PaymentPos::Destroy($id);
        }
        return 'ok';  
    }

    public function bank(Request $request)
    {
        $query = PaymentBank::where('deleted_at', NULL);

        if ($request->name) {
            $query->where('title', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['banks'] =  $query->get();
        }
        else{
            $params['banks'] =  $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();

        return $params;
    }

    public function storebank(Request $request)
    {
        $this->validate($request, [
            'bank_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
        ]);
        
        $bank = PaymentBank::where('deleted_at', NULL)->where('account_number', $request['account_number'])->first();
        if ($bank) {
            return ['Error' => 'Account Number Already Exist'];
        }
        PaymentBank::create([
            'bank_name' => $request['bank_name'],
            'account_number' => $request['account_number'],
            'account_name' => $request['account_name'],
        ]);

        return ['Message' => 'Updated'];
    }


    public function updatebank(Request $request, $id)
    {
        $bank = PaymentBank::findOrFail($id);
        $this->validate($request, [
            'bank_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
        ]);

        $bank->update([
            'bank_name' => $request['bank_name'],
            'account_number' => $request['account_number'],
            'account_name' => $request['account_name'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function destroybank(Request $request)
    {
        foreach ($request->selected as $id) {
            PaymentBank::Destroy($id);
        }
        return 'ok';  
    }

    public function channel(Request $request)
    {
        $query = PaymentChannel::where('id', '!=', NULL);

        if ($request->name) {
            $query->where('title', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['channels'] =  $query->get();
        }
        else{
            $params['channels'] =  $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();
        $params['banks'] = PaymentBank::where('deleted_at', NULL)->get();
        $params['pos'] = PaymentPos::where('deleted_at', NULL)->get();
        return $params;
    }

    public function storechannel(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:191',
        ]);
       
        $channel = PaymentChannel::create([
            'title' => $request['title'],
        ]);

        return ['Message' => 'Updated'];
    }


    public function updatechannel(Request $request, $id)
    {
        $channel = PaymentChannel::findOrFail($id);
        $this->validate($request, [
            'title' => 'required|string|max:191',
        ]);

        $channel->update([
            'title' => $request['title'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function destroychannel(Request $request)
    {
        foreach ($request->selected as $id) {
            PaymentChannel::Destroy($id);
        }
        return 'ok';  
    }

    public function destroymethod(Request $request)
    {
        foreach ($request->selected as $id) {
            Product::Destroy($id);
        }
        return 'ok';  
    }

    public function method(Request $request)
    {
        $params = [];
        $query = Product::with('types')->where('deleted_at', NULL)->latest();
        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->keyword) {
            $query->where('default_esc_payment_products.payment_name', 'like', '%' . $request->keyword . '%')->orWhere('default_esc_payment_products.product_id', $request->keyword);
        }

        if ($request->selected==0) {
            $params['payments'] =  $query->get();
        }
        else{
            $params['payments'] =  $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();
        $params['member_types'] = Type::where('id', '!=', 14)->get();

        return $params;
    }

    public function storemethod(Request $request)
    {
        $this->validate($request, [
            'payment_name' => 'required|string|max:255',
            'amount' => 'required',
        ]);
        $code = rand(3,99688);

        $payment_product = Product::create([
            'payment_name' => ucwords($request->payment_name),
            'amount' => $request->amount,
            'category' => $request->category,
            'door_access' => $request->door_access,
            'grace_period' => $request->grace_period,
            'reoccuring_day' => ($request->type==1) ? $request->reoccuring_day : NULL,
            'type' => $request->type,
            'product_id' => $code,
            'created_by' => auth('api')->user()->id,
        ]);

        $payment_product->types()->attach($request->member_type);

        return ['message' => "Success"];
    }

    public function graceperiod(Request $request)
    {
        $payment_debit = PaymentDebit::find($request->id);
        $payment_debit->grace_period = $payment_debit->grace_period + $request->grace_period;
        $payment_debit->created_by = auth('api')->user()->id;
        $payment_debit->update();
        return ['message' => "Success"];
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

    public function debitmembers(Request $request)
    {
        $this->validate($request, [
            'description' => 'required|string|max:1000',
            'product' => 'required',
            'amount' => 'required',
        ]);

        set_time_limit(0);
        $members = Member::where('deleted_at', NULL)->get();

        $msg = Message::create([
            'user_id' => auth('api')->user()->id,
            'sender_name' => 'ESPORTSCLUB',
            'message' => 'Group Debit Message',
            'page_count' => 1,
        ]);

        foreach ($members as $member) {
            foreach ($request->people_id as $id) {
                if ($id==$member->member_type) {
                    $payment_debit = PaymentDebit::create([
                        'member_id' => $member->id,
                        'product' => $request->product,
                        'amount' => $request->amount,
                        'description' => ucwords($request->description),
                        'debit_type' => $request->debit_type,
                        'grace_period' => $request->grace_period,
                        'start_date' => Carbon::today(),
                        'date_entered' => Carbon::today(),
                        'created_by' => auth('api')->user()->id,
                    ]);

                    $the_member = User::where('unique_id', $member->membership_id)->first();

                    if (($the_member) && ($the_member->wallet_balance >= $request->amount)) {
                        
                        $receipt_number = ($request->receipt_number) ? $request->receipt_number : NULL;
                        $bank = ($request->bank) ? $request->bank : NULL;
                        $pos = ($request->pos) ? $request->pos : NULL;

                        Payment::create([
                            'debit_id' => $payment_debit->id,
                            'member_id' => $payment_debit->member_id,
                            'amount' => $request->amount,
                            'payment_channel' => 6,
                            'created_by' => auth('api')->user()->id,
                            'receipt_number' => $receipt_number,
                            'pos' => $pos,
                            'bank' => $bank,
                        ]);

                        $payment_debit->status = 1;
                        $payment_debit->update();

                        $the_member->wallet_balance = $the_member->wallet_balance - $request->amount;
                        $the_member->update();
                    }
                    else {
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

                            $message = 'Membership ID: '.$the_member->unique_id.'; Debit for '.$request->description.'; Amount: N'.$request->amount;

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
                }
            }
        }
        return ['message' => "Success"];
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

    public function pay(Request $request)
    {
        $paymentDebit = PaymentDebit::find($request->debit_id); 
        $setting = Setting::find(1);
        $user = auth('api')->user();
        
        $account = Account::find($setting->cash_account);
       
        $ledger_id = $this->ledgerID();
        if (!$account->balancing_account) {
            return ['error' => "Please go to chart of account and set the corresponding accounts"];
        }

        if ($request->payment_channel==6) {
            $member = Member::find($request->member_id);
            $userMember = User::where('unique_id', $member->membership_id)->first();
            if ($userMember) {
                if ($userMember->wallet_balance < $request->amount) {
                    return ['error' => "Member do not have upto that amount on his wallet"];
                }
                else{
                    $receipt_number = ($request->receipt_number) ? $request->receipt_number : NULL;
                    $bank = ($request->bank) ? $request->bank : NULL;
                    $pos = ($request->pos) ? $request->pos : NULL;

                    

                    Payment::create([
                        'debit_id' => $request->debit_id,
                        'member_id' => $paymentDebit->member_id,
                        'amount' => $request->amount,
                        'payment_channel' => $request->payment_channel,
                        'created_by' => $user->id,
                        'receipt_number' => $receipt_number,
                        'pos' => $pos,
                        'bank' => $bank,
                    ]);

                    $userMember->wallet_balance = $userMember->wallet_balance - $request->amount;
                    $userMember->update();
                }
            }
            else {
                return ['error' => "Please re-edit member information"];
            }
        }
        else 
        {
            $receipt_number = ($request->receipt_number) ? $request->receipt_number : NULL;
            $bank = ($request->bank) ? $request->bank : NULL;
            $pos = ($request->pos) ? $request->pos : NULL;

            Payment::create([
                'debit_id' => $request->debit_id,
                'member_id' => $paymentDebit->member_id,
                'amount' => $request->amount,
                'payment_channel' => $request->payment_channel,
                'created_by' => $user->id,
                'receipt_number' => $receipt_number,
                'pos' => $pos,
                'bank' => $bank,
            ]);
        }

        $paymentDebit->status = 1;
        $paymentDebit->update();
        

        if ($request->amount < $paymentDebit->amount) {
            PaymentDebit::create([
                'description' => 'Debt unpaid from Debt ID '. $paymentDebit->id,
                'member_id' => $paymentDebit->member_id,
                'amount' => $paymentDebit->amount-$request['amount'],
                'grace_period' => 5,
                'debit_type' => 0,
                'start_date' => Carbon::today(),
                'date_entered' => Carbon::today(),
                'created_by' => auth('api')->user()->id,
            ]);
        }

        Ledger::create([
            'ledger_id' => $ledger_id,
            'ledger_date' => Carbon::now(),
            'user_id' => $user->id,
            'amount' => $request->amount,
            'debit' => $account->id,
            'credit' => $account->balancing_account->id,
            'trans_id' => $request->debit_id,
            'description' => $request->description,
        ]);
        return 'ok';
    }

    public function getStore($code)
    {
        $store = Store::where('deleted_at', NULL)->where('code', $code)->first();
        if ($store) {
            return $store;
        }
    }

    public function show(Request $request, $code, $id)
    {
        
        $params = [];
        $query = InventoryStore::where('inventory_store.deleted_at', NULL)->where('inventory_store.store_id', $id)
            ->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->join('categories', 'inventories.category', '=', 'categories.id')
            ->where('categories.deleted_at', NULL)
            ->where('inventories.deleted_at', NULL);

            if ($request->orderName==0) {
                $query->orderBy('inventories.product_name', 'Desc');
            }
            elseif ($request->orderName==1) {
                $query->orderBy('inventories.product_name', 'asc');
            }

            if ($request->orderCategory==0) {
                $query->orderBy('categories.name', 'Desc');
            }
            elseif ($request->orderCategory==1) {
                $query->orderBy('categories.name', 'asc');
            }

            if ($request->orderAmount==0) {
                $query->orderBy('inventories.price', 'Desc');
            }
            elseif ($request->orderAmount==1) {
                $query->orderBy('inventories.price', 'asc');
            }

            if ($request->orderQuantity==0) {
                $query->orderBy('inventory_store.number', 'Desc');
            }
            elseif ($request->orderQuantity==1) {
                $query->orderBy('inventory_store.number', 'asc');
            }

            if ($request->orderThreshold==0) {
                $query->orderBy('inventories.threshold', 'Desc');
            }
            elseif ($request->orderThreshold==1) {
                $query->orderBy('inventories.threshold', 'asc');
            }

            if ($request->orderPeriod==0) {
                $query->orderBy('inventory_store.updated_at', 'Desc');
            }
            elseif ($request->orderPeriod==1) {
                $query->orderBy('inventory_store.updated_at', 'asc');
            }

            if ($request->name) {
                $query->where('inventories.product_name', 'like', '%' . $request->name . '%')->orWhere('inventories.product_id', 'like', '%' . $request->name . '%')->orWhere('categories.name', 'like', '%' . $request->name . '%');
            }

            //return $request;
            $query->select(
                'inventories.id as id',
                'inventories.category as category',
                'categories.name as name',
                'inventories.product_id as product_id',
                'inventories.product_name as product_name',
                'inventory_store.number as quantity',
                'inventory_store.id as room_id',
                'inventory_store.updated_at as updated_at',
                'inventories.cost_price as cost_price',
                'inventories.price as price',
                'inventories.unit as unit',
                'inventories.threshold as threshold'
            );

            if ($request->selected==0) {
                $params['inventories'] =  $query->get();
            }
            else{
                $params['inventories']  =  $query->paginate($request->selected);
            }
        
        $query1 = InventoryStore::where('inventory_store.deleted_at', NULL)->where('inventory_store.store_id', $id)
            ->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->join('categories', 'inventories.category', '=', 'categories.id')
            ->where('inventories.deleted_at', NULL)
            ->where('categories.deleted_at', NULL);
            if ($request->name) {
                $query1->where('inventories.product_name', 'like', '%' . $request->name . '%')->orWhere('inventories.product_id', 'like', '%' . $request->name . '%')->orWhere('categories.name', 'like', '%' . $request->name . '%');
            }

        $params['all'] = $query1->count();

        return $params;
    }

    public function showroom(Request $request, $code, $id)
    {        
        $params = [];
        $query = Room::where('rooms.deleted_at', NULL)->where('rooms.store_id', $id)
            ->join('inventories', 'rooms.inventory_id', '=', 'inventories.id')
            ->join('categories', 'inventories.category', '=', 'categories.id')
            ->where('categories.deleted_at', NULL)
            ->where('inventories.deleted_at', NULL)
            ->orderBy('inventories.created_at', 'desc');
            if ($request->name) {
                $query->where('inventories.product_name', 'like', '%' . $request->name . '%')->orWhere('inventories.product_id', 'like', '%' . $request->name . '%')->orWhere('categories.name', 'like', '%' . $request->name . '%');
            }

            //return $request;
            $query->select(
                'inventories.id as id',
                'inventories.category as category',
                'categories.name as name',
                'inventories.product_id as product_id',
                'inventories.product_name as product_name',
                'rooms.id as room_id',
                'rooms.number as quantity',
                'rooms.updated_at as updated_at',
                'inventories.cost_price as cost_price',
                'inventories.price as price',
                'inventories.unit as unit',
                'inventories.threshold as threshold'
            );

            if ($request->selected==0) {
                $params['inventories'] =  $query->get();
            }
            else{
                $params['inventories']  =  $query->paginate($request->selected);
            }
        
        $query1 = Room::where('rooms.deleted_at', NULL)->where('rooms.store_id', $id)
            ->join('inventories', 'rooms.inventory_id', '=', 'inventories.id')
            ->join('categories', 'inventories.category', '=', 'categories.id')
            ->where('inventories.deleted_at', NULL)
            ->where('categories.deleted_at', NULL);
            if ($request->name) {
                $query1->where('inventories.product_name', 'like', '%' . $request->name . '%')->orWhere('inventories.product_id', 'like', '%' . $request->name . '%')->orWhere('categories.name', 'like', '%' . $request->name . '%');
            }

        $params['all'] = $query1->count();

        return $params;
    }

    public function storemovement(Request $request)
    {
        $user = auth('api')->user();
        $params = [];

        $query = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->orderBy('rooom_movement.created_at', 'desc')
            ->select(
                'rooom_movement.id as id',
                'rooom_movement.product_id as product_id',
                'rooom_movement.title as title',
                'rooom_movement.qty as quantity',
                'rooom_movement.status as status',
                'rooom_movement.moved as moved',
                'rooom_movement.user_id as user_id',
                'rooom_movement.approved_by as approved_by',
                'rooom_movement.manager_id as manager_id',
                'rooom_movement.updated_at as updated_at',
                'stores.name as store_name'
            );

            if ($request->store_id) {
                $query->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query->where('rooom_movement.status', $request->status);
            }
            
            if ($request->selected==0) {
                $params['inventories'] =  $query->get();
            }
            else{
                $params['inventories']  =  $query->paginate($request->selected);
            }
            
        $query1 = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL);
            if ($request->store_id) {
                $query1->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query1->where('rooom_movement.status', $request->status);
            }

        $params['all'] = $query1->count();

        return $params;
    }

    public function mymovement(Request $request)
    {
        $user = auth('api')->user();
        $params = [];

        $query = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.moved', $user->store)
            ->where('rooom_movement.status', '!=', 'unpproved')
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->orderBy('rooom_movement.created_at', 'desc')               
            ->select(
                'rooom_movement.id as id',
                'rooom_movement.product_id as product_id',
                'rooom_movement.qty as quantity',
                'rooom_movement.title as title',
                'rooom_movement.status as status',
                'rooom_movement.moved as moved',
                'rooom_movement.user_id as user_id',
                'rooom_movement.available as available',
                'rooom_movement.approved_by as approved_by',
                'rooom_movement.manager_id as manager_id',
                'stores.name as store_name'
            );

            if ($request->store_id) {
                $query->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query->where('rooom_movement.status', $request->status);
            }
            
            if ($request->selected==0) {
                $params['inventories'] =  $query->get();
            }
            else{
                $params['inventories']  =  $query->paginate($request->selected);
            }
            
        $query1 = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.moved', $user->store)
            ->where('rooom_movement.status', '!=', 'unpproved')
            ->join('inventory_store', 'rooom_movement.product_id', '=', 'inventory_store.id')
            ->where('inventory_store.deleted_at', NULL)
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL);
            if ($request->store_id) {
                $query1->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query1->where('rooom_movement.status', $request->status);
            }

        $params['all'] = $query1->count();

        return $params;
    }

    public function myrequest(Request $request)
    {
        $user = auth('api')->user();
        $params = [];

        $query = RoomMovement::where('deleted_at', NULL)
            ->where('store_id', $user->store)->latest();

            if ($request->store_id) {
                $query->where('store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query->where('status', $request->status);
            }
            
            if ($request->selected==0) {
                $params['inventories'] =  $query->get();
            }
            else{
                $params['inventories']  =  $query->paginate($request->selected);
            }
            
        $query1 = RoomMovement::where('deleted_at', NULL)
            ->where('store_id', $user->store);

            if ($request->store_id) {
                $query1->where('store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query1->where('status', $request->status);
            }

        $params['all'] = $query1->count();

        return $params;
    }

    public function allmovement(Request $request)
    {
        $user = auth('api')->user();
        $params = [];

        $query = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.status', '!=', 'unapproved')
            ->join('inventories', 'rooom_movement.product_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->orderBy('rooom_movement.created_at', 'desc')
            ->select(
                'rooom_movement.id as id',
                'rooom_movement.product_id as product_id',
                'rooom_movement.qty as quantity',
                'rooom_movement.title as title',
                'rooom_movement.status as status',
                'rooom_movement.moved as moved',
                'rooom_movement.user_id as user_id',
                'rooom_movement.approved_by as approved_by',
                'rooom_movement.manager_id as manager_id',
                'stores.name as store_name'
            );

            if ($request->store_id) {
                $query->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query->where('rooom_movement.status', $request->status);
            }
            
            if ($request->selected==0) {
                $params['inventories'] =  $query->get();
            }
            else{
                $params['inventories']  =  $query->paginate($request->selected);
            }
            
        $query1 = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.status', '!=', 'unapproved')
            ->join('inventories', 'rooom_movement.product_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL);
            if ($request->store_id) {
                $query1->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query1->where('rooom_movement.status', $request->status);
            }

        $params['all'] = $query1->count();

        return $params;
    }

    public function allrequest(Request $request)
    {
        $user = auth('api')->user();
        $params = [];

        $query = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.status', '!=', 'unpproved')
            ->where('rooom_movement.type', 0)
            ->join('inventory_store', 'rooom_movement.product_id', '=', 'inventory_store.id')
            ->where('inventory_store.deleted_at', NULL)
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->orderBy('rooom_movement.created_at', 'desc')
            ->select(
                'rooom_movement.id as id',
                'rooom_movement.product_id as product_id',
                'rooom_movement.qty as quantity',
                'rooom_movement.title as title',
                'rooom_movement.status as status',
                'rooom_movement.moved as moved',
                'rooom_movement.user_id as user_id',
                'rooom_movement.approved_by as approved_by',
                'rooom_movement.manager_id as manager_id',
                'stores.name as store_name'
            );

            if ($request->store_id) {
                $query->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query->where('rooom_movement.status', $request->status);
            }
            
            if ($request->selected==0) {
                $params['inventories'] =  $query->get();
            }
            else{
                $params['inventories']  =  $query->paginate($request->selected);
            }
            
        $query1 = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.status', '!=', 'unpproved')
            ->where('rooom_movement.type', 0)
            ->join('inventory_store', 'rooom_movement.product_id', '=', 'inventory_store.id')
            ->where('inventory_store.deleted_at', NULL)
            ->join('stores', 'rooom_movement.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL);
            if ($request->store_id) {
                $query1->where('rooom_movement.store_id', $request->store_id);
            }
        
            if ($request->status) {
                $query1->where('rooom_movement.status', $request->status);
            }

        $params['all'] = $query1->count();

        return $params;
    }

    public function getInventory(Request $request)
    {
        //return $request->name;
        $query = DB::table('inventory_store')->where('inventory_store.deleted_at', NULL);

            if ($request->store_code) {
                $get_store = Store::where('deleted_at', NULL)->where('code', $request->store_code)->first();
                $myaccount = User::find(auth('api')->user()->id);
                $myaccount->store = $get_store->id;
                $myaccount->update();
                
                $query->where('inventory_store.store_id', $get_store->id);
            }
            else
            {
                $query->where('inventory_store.store_id', auth('api')->user()->store);
            }

            if ($request->name) {
                $query->where('inventories.product_name', 'like', '%' . $request->name . '%');
            }

            $query->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL);
            
        $inventory = $query->paginate(10);
        return response()->json($inventory);
    }

    public function gettotal(Request $request)
    {
        $get_price = array();
        foreach ($request->productItems as $product) {
            $each_price = ($product['qty'] * $product['price'])-(($product['discount']/100) * ($product['qty'] * $product['price']));
            array_push($get_price, $each_price);
        }

        foreach ($request->serviceItems as $service) {
            $each_price = $service['price'];
            array_push($get_price, $each_price);
        }
        return array_sum($get_price);
    }

    public function getnumber(Request $request)
    {
        $item = Inventory::where('deleted_at', NULL)->where('product_id', $request->inventory)->first();
        if ($item) {
            $inventory = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $item->id)->where('store_id', $request->store)->first();
        }
        else {
            $inventory = NULL;
        }

        return $inventory->number;
    }

    public function allInventory(Request $request)
    {
        //return $request->name;
        $query = DB::table('inventory_store')->where('inventory_store.deleted_at', NULL);

            if ($request->store_code) {
                $get_store = Store::where('deleted_at', NULL)->where('code', $request->store_code)->first();
                $myaccount = User::find(auth('api')->user()->id);
                $myaccount->store = $get_store->id;
                $myaccount->update();
                
                $query->where('inventory_store.store_id', $get_store->id);
            }
            else
            {
                $query->where('inventory_store.store_id', auth('api')->user()->store);
            }

            $query->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL);
            
        $inventory = $query->get();
        return response()->json($inventory);
    }

    public function tradeinventory(Request $request)
    {
        $query = DB::table('inventory_store')->where('inventory_store.deleted_at', NULL);

            if ($request->store_code) {
                $get_store = Store::where('deleted_at', NULL)->where('code', $request->store_code)->first();
                $myaccount = User::find(auth('api')->user()->id);
                $myaccount->store = $get_store->id;
                $myaccount->update();
                
                $query->where('inventory_store.store_id', $get_store->id);
            }
            else
            {
                $query->where('inventory_store.store_id', auth('api')->user()->store);
            }

            $query->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL);
            
        $inventory = $query->get();
        return response()->json($inventory);
    }

    public function loadinventory(Request $request)
    {
        $search_term = $request[0];
        $inventory = InventoryStore::where('inventory_store.deleted_at', NULL)
            ->where('inventory_store.store_id', auth()->user()->store)
            ->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.product_name', 'like', '%' . $search_term . '%')
            ->where('inventories.deleted_at', NULL)
            ->get();
        return $inventory;
    }

    public function discharge()
    {
        $inventory = StoreRequest::where('store_request.deleted_at', NULL)
            ->join('inventories', 'store_request.product_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->join('stores', 'store_request.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->orderBy('store_request.created_at', 'desc')
            ->select(
                'store_request.req_id as req_id',
                'stores.name as store_name',
                'store_request.id as id',
                'inventories.product_name as product_name',
                'inventories.quantity as quantity',
                'store_request.qty as qty',
                'store_request.sender_id as sender_id',
                'store_request.created_at as created_at',
                'store_request.approved_by as approved_by',
                'store_request.reciever_id as reciever_id',
                'store_request.status as status'
            )
            ->groupBy('store_request.req_id')
        ->paginate(10);
        return response()->json($inventory);
    }  

    public function approve(Request $request, $id)
    {
        $store_request = StoreRequest::where('deleted_at')->find($id);
        $product = Inventory::where('deleted_at')->find($store_request->product_id);

        $this->validate($request, [
            //'qty' => 'required',
        ]);

        if ($product->quantity < $store_request->qty) {
            return ['error' => "You cannot disburse more than you dont have in your warehouse"];
        }
        $store_request->update([
            'qty' => $request->qty,
            'approved_by' => auth('api')->user()->id,
            'status' => 'approved',
        ]);
        return ['message' => "Success"];
    }

    public function decline($id)
    {
        $store_request = StoreRequest::where('deleted_at')->find($id);
    
        $store_request->update([
            'status' => 'declined',
        ]);
        return ['message' => "Success"];
    }

    public function accept($id)
    {
        $req = StoreRequest::where('deleted_at')->find($id);

        $inventory = Inventory::where('deleted_at', NULL)->find($req->product_id);
        if ($inventory) {
            $inventory->quantity = $inventory->quantity - $req->qty;
            $inventory->update();

            $store_inventory = DB::table('inventory_store')
                ->where('deleted_at', NULL)
                ->where('store_id', $req->store_id)
                ->where('inventory_id', $req->product_id)
                ->first();

            $store_inventory_update = DB::table('inventory_store')
                ->where('deleted_at', NULL)
                ->where('store_id', $req->store_id)
                ->where('inventory_id', $req->product_id)
                ->update(['number' => $store_inventory->number + $req->qty, 'threshold' => $inventory->threshold]);

            //Updating Status
            $req->reciever_id = auth('api')->user()->id;
            $req->status = 'concluded';
            $req->update();
            return $req;
        }
        else
        {
            return ['error' => "Product does not exist!!!"];
        }
        
    }

    public function acceptall(Request $request)
    {
        foreach ($request->productItems as $item) {
            $req = StoreRequest::where('deleted_at')->find($item['id']);

            $inventory = Inventory::where('deleted_at', NULL)->find($req->product_id);
            if ($inventory) {
                $inventory->quantity = $inventory->quantity - $req->qty;
                $inventory->update();

                $store_inventory = DB::table('inventory_store')
                    ->where('deleted_at', NULL)
                    ->where('store_id', $req->store_id)
                    ->where('inventory_id', $req->product_id)
                    ->first();

                $store_inventory_update = DB::table('inventory_store')
                    ->where('deleted_at', NULL)
                    ->where('store_id', $req->store_id)
                    ->where('inventory_id', $req->product_id)
                    ->update(['number' => $store_inventory->number + $req->qty, 'threshold' => $inventory->threshold]);

                //Updating Status
                $req->reciever_id = auth('api')->user()->id;
                $req->status = 'concluded';
                $req->update();
                return $req;
            }
        }
    }
    
    public function storereq(Request $request){
        $random_number = rand(2,988888888);

        foreach ($request->productItems as $item) {
            $StoreRequest = StoreRequest::create([
                'req_id' => $random_number,
                'product_id' => $item['id'],
                'store_id' => auth('api')->user()->store,
                'qty' => $item['quantity'],
                'status' => 'pending',
                'sender_id' => auth('api')->user()->id,
            ]);
        }
        return StoreRequest::where('req_id', $random_number)->first();
    }


    public function updatemethod(Request $request, $id)
    {
        $payment_product = Product::where('deleted_at')->find($id);
        $this->validate($request, [
            'payment_name' => 'required|string|max:255',
            'amount' => 'required',
        ]);

        $payment_product->update([
            'payment_name' => ucwords($request->payment_name),
            'amount' => $request->amount,
            'category' => $request->category,
            'door_access' => $request->door_access,
            'grace_period' => $request->grace_period,
            'reoccuring_day' => ($request->type==1) ? $request->reoccuring_day : NULL,
            'type' => $request->type,
            'created_by' => auth('api')->user()->id,
        ]);

        $payment_product->types()->sync($request->member_type);
        //$payment_product->types()->attach($request->member_type);
        return ['message' => "Success"];
    }

    public function destroy(Request $request)
    {
       foreach ($request->selected as $id) {
            if ($id!=1) {
                Store::Destroy($id);
            }
        }
        return 'ok';
    }

    public function initiate(Request $request)
    {
        $ref_id = rand(1, 888888);

        foreach ($request->selected as $id) {
            $product = InventoryStore::find($id);
            if (($product->number!=NULL) && ($product->number>0)) {
                $movement = new RoomMovement ();
                $movement->ref_id = $ref_id;
                $movement->moved = auth('api')->user()->store;
                $movement->user_id = auth('api')->user()->id;
                $movement->product_id = $id;
                $movement->status = 'unapproved';
                $movement->move_type = 1;
                $movement->save(); 
            }
        }
        return $ref_id;
    }

    public function requestinitiate(Request $request)
    {
        $ref_id = rand(1, 888888);

        foreach ($request->selected as $id) {
            $product = InventoryStore::find($id);   
            $movement = new RoomMovement ();
            $movement->ref_id = $ref_id;
            $movement->store_id = auth('api')->user()->store;
            $movement->user_id = auth('api')->user()->id;
            $movement->product_id = $id;
            $movement->status = 'unapproved';
            $movement->type = 0;
            $movement->save(); 
        }
        return $ref_id;
    }

    public function updatemovement(Request $request)
    {
        foreach ($request->productItems as $item) {
            $product = RoomMovement::find($item['id']);
            if (($item['qty']!=NULL) && ($item['number'] >= $item['qty'])) {
         
                $product->store_id = $request->store;
                $product->qty = $item['qty'];
                $product->type = 1;
                $product->status = 'pending approval';
                $product->update();
            }
        }
        return $request;
    }

    public function updatereq(Request $request)
    {
        foreach ($request->productItems as $item) {
            $product = RoomMovement::find($item['id']);
            $product->moved = $item['store'];
            $product->qty = $item['qty'];
            $product->type = 0;
            $product->status = 'requested';
            $product->update();
        }
        return $request;
    }

    public function getmovements($ref_id)
    {
        $params = [];
        $params['products'] = RoomMovement::where('deleted_at', NULL)
            ->where('ref_id', $ref_id)->get();
        return $params;
    }

    public function reqdeleteall(Request $request)
    {
        foreach ($request->selected as $id) {

            $room = RoomMovement::find($id);
            if ($room->status == 'requested') {
                RoomMovement::destroy($id);
            }
        }
        return 'ok';
    }

    public function sacceptall(Request $request)
    {
        foreach ($request->selected as $id) {
            $room = RoomMovement::find($id);
            if ($room->status == 'approved') {

                $room->status = 'accepted';
                $room->manager_id = auth('api')->user()->id;
                $room->update();

                $inventory = InventoryStore::where('deleted_at', NULL)->find($room->product_id);
                $inventory->number = $inventory->number + $room->qty;
                $inventory->update();
            }
        }
        return 'ok';
    }

    public function srejectall(Request $request)
    {
       foreach ($request->selected as $id) {
            
            $room = RoomMovement::find($id);
            if ($room->status == 'approved') {
                $room->manager_id = auth('api')->user()->id;
                $room->status = 'rejected';
                $room->update();

                $inventory = InventoryStore::where('deleted_at', NULL)->find($room->product_id);
      

                $main = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $inventory->inventory_id)->where('store_id', $room->getOriginal('moved'))->first();

                $main->number = $main->number + $room->qty;
                $main->update();
            }
        }
        return 'ok';
    }

    public function outletacceptall(Request $request)
    {
        foreach ($request->selected as $item) {
            $room = RoomMovement::find($item);
            if ($room->status == 'pending approval' || $room->status == 'requested') {
                $room->status = 'approved';
                $room->approved_by = auth('api')->user()->id;
                $room->update();

                $rec = InventoryStore::where('deleted_at', NULL)->find($room->product_id);
                $rec_inventory = $rec->inventory_id;

                $inventory = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $rec_inventory)->where('store_id', $room->getOriginal('moved'))->first();
                $inventory->number = $inventory->number - $room->qty;
                $inventory->update();
            }
        }
        return 'ok';
    }

    public function outletrejectall(Request $request)
    {
       foreach ($request->selected as $id) {
            
            $room = RoomMovement::find($id);
            if ($room->status == 'pending approval' || $room->status == 'requested') {
                $room->approved_by = auth('api')->user()->id;
                $room->status = 'not approved';
                $room->update();
            }
        }
        return 'ok';
    }

    


    public function reports(Request $request)
    {
        $params = [];

        $query = Sale::where('sales.deleted_at', NULL);

        //Optional where
        if ($request->start_date) {
            $query->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query->where('sales.user_id', $request->staff);
        }

        $report_data = $query->where('sales.status', 'pending')->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'sales.id as id',
                'sales.sale_id as sale_id',
                'users.name as user_name',
                'sales.initialPrice as initialPrice',
                'sales.totalPrice as totalPrice',
                'sales.discount as discount',
                'sales.main_date as created_at'  
            )->paginate(10);

        $params['report_data'] = $report_data;
        $params['users'] = User::where('deleted_at', NULL)->where('role', '!=', 0)->get();

        return $params;
    }

    public function orders(Request $request)
    {
        $params = [];

        $query = Sale::where('sales.deleted_at', NULL);

        //Optional where
        if ($request->start_date) {
            $query->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query->where('sales.user_id', $request->staff);
        }

        if ($request->buyer) {
            $query->where('sales.buyer', $request->buyer);
        }

        if ($request->store) {
            $query->where('sales.store_id', $request->store);
        }

        if ($request->customer) {
            $query->where('users.name', 'like', '%' . $request->customer . '%');
        }

        $query->where('sales.status', '!=', 'pending')->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.buyer', '=', 'users.id')
            ->select(
                'sales.id as id',
                'sales.sale_id as sale_id',
                'users.name as user_name',
                'sales.initialPrice as initialPrice',
                'sales.totalPrice as totalPrice',
                'sales.discount as discount',
                'sales.mop as mop',
                'sales.store_id as store_id',
                'sales.status as status',
                'sales.main_date as created_at'  
            );

            if ($request->selected==0) {
                $params['report_data'] =  $query->get();
            }
            else{
                $params['report_data']  =  $query->paginate($request->selected);
            }
         
        $query1 = Sale::where('sales.deleted_at', NULL);

        //Optional where
        if ($request->start_date) {
            $query1->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query1->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query1->where('sales.user_id', $request->staff);
        }

        if ($request->buyer) {
            $query1->where('sales.buyer', $request->buyer);
        }

        if ($request->customer) {
            $query1->where('users.name', 'like', '%' . $request->customer . '%');
        }

        $params['all'] = $query1->where('sales.status', '!=', 'pending')->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.buyer', '=', 'users.id')->count();

        $params['users'] = User::where('deleted_at', NULL)->where('role', '!=', 0)->get();

        return $params;
    }

    public function quotes(Request $request)
    {
        $params = [];

        $query = Sale::where('sales.deleted_at', NULL);

        //Optional where
        if ($request->start_date) {
            $query->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query->where('sales.user_id', $request->staff);
        }

        if ($request->buyer) {
            $query->where('sales.buyer', $request->buyer);
        }

        if ($request->customer) {
            $query->where('users.name', 'like', '%' . $request->customer . '%');
        }

        $query->where('sales.status', '=', 'pending')->where('sales.approved', '=', 0)->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.buyer', '=', 'users.id')
            ->select(
                'sales.id as id',
                'sales.sale_id as sale_id',
                'users.name as user_name',
                'sales.initialPrice as initialPrice',
                'sales.totalPrice as totalPrice',
                'sales.discount as discount',
                'sales.mop as mop',
                'sales.status as status',
                'sales.main_date as created_at'  
            );

            if ($request->selected==0) {
                $params['report_data'] =  $query->get();
            }
            else{
                $params['report_data']  =  $query->paginate($request->selected);
            }
         
        $query1 = Sale::where('sales.deleted_at', NULL);

        //Optional where
        if ($request->start_date) {
            $query1->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query1->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query1->where('sales.user_id', $request->staff);
        }

        if ($request->buyer) {
            $query1->where('sales.buyer', $request->buyer);
        }

        if ($request->customer) {
            $query1->where('users.name', 'like', '%' . $request->customer . '%');
        }

        $params['all'] = $query1->where('sales.status', '=', 'pending')
            ->where('sales.approved', '=', 0)
            ->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.buyer', '=', 'users.id')
            ->count();

        $params['users'] = User::where('deleted_at', NULL)->where('role', '!=', 0)->get();

        return $params;
    }

    public function invoice(Request $request)
    {
        $params = [];

        $query = Sale::where('sales.deleted_at', NULL);

        //Optional where
        if ($request->start_date) {
            $query->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query->where('sales.user_id', $request->staff);
        }

        if ($request->buyer) {
            $query->where('sales.buyer', $request->buyer);
        }

        if ($request->customer) {
            $query->where('users.name', 'like', '%' . $request->customer . '%');
        }

        $query->where('sales.status', '=', 'pending')->where('sales.approved', '=', 1)->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.buyer', '=', 'users.id')
            ->select(
                'sales.id as id',
                'sales.sale_id as sale_id',
                'users.name as user_name',
                'sales.initialPrice as initialPrice',
                'sales.totalPrice as totalPrice',
                'sales.discount as discount',
                'sales.mop as mop',
                'sales.status as status',
                'sales.main_date as created_at'  
            );

            if ($request->selected==0) {
                $params['report_data'] =  $query->get();
            }
            else{
                $params['report_data']  =  $query->paginate($request->selected);
            }
         
        $query1 = Sale::where('sales.deleted_at', NULL);

        //Optional where
        if ($request->start_date) {
            $query1->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query1->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query1->where('sales.user_id', $request->staff);
        }

        if ($request->buyer) {
            $query1->where('sales.buyer', $request->buyer);
        }

        if ($request->customer) {
            $query1->where('users.name', 'like', '%' . $request->customer . '%');
        }

        $params['all'] = $query1->where('sales.status', '=', 'pending')
            ->where('sales.approved', '=', 1)
            ->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.buyer', '=', 'users.id')
            ->count();

        $params['users'] = User::where('deleted_at', NULL)->where('role', '!=', 0)->get();

        return $params;
    }

    public function debtors(Request $request)
    {
        $params = [];
        
        $query = Debtor::where('debtors.deleted_at', NULL)->where('debtors.type', 1)->orderBy('debtors.created_at', 'desc');

        if ($request->keyword) {
            $query->where('debtors.trans_id', $request->keyword);
        }

        if ($request->user_id) {
            $query->where('debtors.user_id', $request->user_id);
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
        

        $query1 = Debtor::where('debtors.deleted_at', NULL)->where('debtors.type', 1);

        if ($request->keyword) {
            $query1->where('debtors.trans_id', $request->keyword);
        }

        if ($request->user_id) {
            $query1->where('debtors.user_id', $request->user_id);
        }

        if ($request->status) {
            $query1->where('debtors.status', $request->status);
        }
        $params['all'] = $query1->count();

        return $params;
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
                $message = 'Complete payment of sales of Items with ID '. $debtor->trans_id;
            }
            else{
                $message = 'Part payment of sales of Items with ID '. $debtor->trans_id;
            }

            $ledger_id = $this->ledgerID();
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 33,
                'amount' => $request->amount_paid,
                'debit' => $request->amount_paid,
                'credit' => 0,
                'description' => $message,
                'position' => 1,
            ]);

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::today(),
                'account' => 34,
                'amount' => $request->amount_paid,
                'debit' => 0,
                'credit' => $request->amount_paid,
                'description' => $message,
                'position' => 2,
            ]);
        }
        return $debtor;
    }
    public function debtorview($sale_id)
    {
        $params = [];
        $query = Debtor::where('debtors.deleted_at', NULL)->where('debtors.trans_id', $sale_id);

        $report_data = $query->orderBy('debtors.created_at', 'Desc')
            ->join('sales', 'debtors.trans_id', '=', 'sales.sale_id')
            ->join('users', 'debtors.user_id', '=', 'users.id')
            ->select(
                'sales.sale_id as sale_id',
                'users.name as customer',
                'sales.cart as cart',
                'sales.totalPrice as totalPrice',
                'sales.amount_paid as amount_paid',
                'debtors.amount as amount',
                'debtors.amount_paid as amount_paid',
                'debtors.status as status'
            )->paginate(10);

        $report_data->transform(function($order, $key){
            $order->cart = unserialize($order->cart);
            return $order;
        });

        $params['report_data'] = $report_data;
        return $params;
    }

    public function addDebt(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
        ]);

        $first_debt = Debtor::where('deleted_at', NULL)->where('trans_id', $request->sale_id)->where('status', 0)->first();
        $first_debt->amount_paid = $request->amount;
        $first_debt->update();


        $first_debt_id = $first_debt->id;
        $first_debt_amount = $first_debt->amount;

        $debts = Debtor::where('deleted_at', NULL)->where('trans_id', $request->sale_id)->get();
        foreach ($debts as $debt) {
            $debtor = Debtor::where('deleted_at', NULL)->find($debt->id);
            $debtor->status = 1;
            $debtor->update();
        }

        if ($first_debt_amount != $request->amount) {
            $set_debt = new Debtor();
            $set_debt->user_id = $debtor->getOriginal('user_id');
            $set_debt->trans_id = $request->sale_id;
            $set_debt->amount = $first_debt_amount - $request->amount;
            $set_debt->status = 0;
            $set_debt->save();
        }

        $ledger_id = $this->ledgerID();

        $account_one = Account::where('deleted_at', NULL)->find($request->account_one);
        $account_type_one = AccountType::where('deleted_at', NULL)->find($account_one->type);

        $account_two = Account::where('deleted_at', NULL)->find($request->account_two);
        $account_type_two = AccountType::where('deleted_at', NULL)->find($account_two->type);

        Ledger::create([
            'ledger_id' => $ledger_id,
            'ledger_date' => Carbon::today(),
            'account' => $request->account_one,
            'amount' => $request->amount,
            'debit' => $request->amount,
            'credit' => 0,
            'description' => 'Debt payment with sales ID'.$request->sale_id,
            'position' => 1,
        ]);

        Ledger::create([
            'ledger_id' => $ledger_id,
            'ledger_date' => Carbon::today(),
            'account' => $request->account_two,
            'amount' => $request->amount,
            'debit' => 0,
            'credit' => $request->amount,
            'description' => 'Debt payment with sales ID '.$request->sale_id,
            'position' => 2,
        ]);

        return 'ok';
    }
}
