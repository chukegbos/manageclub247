<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Fund;
use App\Ledger;
use App\Login;
use App\Account;
use App\AccountType;
use App\Setting;
use App\Supplier;
use App\Credit;
use App\Kitchen;
use App\Inventory;
use App\FoodInventory;
use App\Member;
use App\InventoryStore;
use App\FoodKitchen;
use App\Food;
use App\Store;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use App\Bank;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];
        $query = User::where('deleted_at', NULL)->where('role', '!=', 0)->where('role', '!=', 1);

        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%')->orWhere('phone', 'like', '%' . $request->name . '%')->orWhere('email', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['staff'] =  $query->get();
        }
        else{
            $params['staff'] =  $query->paginate($request->selected);
        }
        $query1 = User::where('deleted_at', NULL)->where('role', '!=', 0);
        $params['all'] = $query1->count();
        return $params;
    }

    public function allusers(Request $request)
    {
        $search_term = $request[0];
        $users = User::where('deleted_at', NULL)
            ->latest()
            ->get();
        return response()->json($users);
    }

    public function setting()
    {
        return Setting::find(1); 
    }

    public function updateSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $this->validate($request, [
            'sitename' => 'required|string|max:191',
            'email' => 'required|string',
            'phone' => 'required|string|max:19',
            'admin_percent' => 'required|string|max:2',
            'manager_percent' => 'required|string|max:2',
            'cashier_percent' => 'required|string|max:3',
            //'naira_value' => 'required|string|max:255',
            //'expense_ratio' => 'required|string|max:255',
            //'percent_gain' => 'required',
        ]);
        
        $setting->update([
            'sitename' => $request['sitename'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'admin_percent' => $request['admin_percent'],
            'cashier_percent' => $request['cashier_percent'],
            'manager_percent' => $request['manager_percent'],
            'naira_value' => $request['naira_value'],
            'expense_ratio' => $request['expense_ratio'],
            'percent_gain' => $request['percent_gain'],
            'cash_account' => $request['cash_account'],
            'credit_account' => $request['credit_account'],
            'purchase_cash_account' => $request['purchase_cash_account'],
            'purchase_credit_account' => $request['purchase_credit_account'],
            
        ]);

        $users = User::where('deleted_at', NULL)->get();
        foreach ($users as $user) {
            $get_user = User::find($user->id);
            if ($get_user->role == 1) {
                $get_user->sale_percent = $request['cashier_percent'];
            }
            elseif ($get_user->role == 2) {
                $get_user->sale_percent = $request['manager_percent'];
            }
            else{
                $get_user->sale_percent = $request['admin_percent'];
            }
            $get_user->update();         
        }

        return ['Message' => 'Updated'];
    }

    public function bank()
    {
        return Bank::where('deleted_at', NULL)->get(); 
    }

    public function fetchbank(Request $request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.paystack.co/bank/resolve?account_number='.$request->account_number.'&bank_code='.$request->bank_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: Bearer sk_test_2b3f7792faa550ac09dd009b1788b89f3286c3a4",
              "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } 
        else {
            return $response;
        }
    }

    public function getUser(Request $request)
    {
        $params = [];
        $params['customers'] = Member::where('deleted_at', NULL)->get();
        $params['user'] = auth('api')->user();
        //$params['user_store'] = $params['user']->getOriginal('store');

        $params['accounts'] = Account::where('deleted_at', NULL)->get();
        $params['types'] = AccountType::where('deleted_at', NULL)->get();
        $params['products'] = Inventory::where('deleted_at', NULL)->get();
        $params['stewards'] = User::where('deleted_at', NULL)->where('role', 8)->orWhere('role', 7)->get();
        $params['items'] = FoodInventory::where('deleted_at', NULL)->get();
        $params['stores'] = Store::where('deleted_at', NULL)->where('id', '!=', 1)->get();
        $params['kitchens'] = Kitchen::where('deleted_at', NULL)->get();
        $params['suppliers'] = Supplier::where('deleted_at', NULL)->get();
        $params['login'] = Login::where('user_id', auth('api')->user()->id)->where('logout', '!=', NULL)->where('verified_by', NULL)->first();
        $params['inventory'] = InventoryStore::where('inventory_store.deleted_at', NULL)
            ->where('inventory_store.store_id',  auth()->user()->getOriginal('store'))
            ->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->get();

        //$params['foods'] = Food::get();

        $params['foods'] = FoodKitchen::where('food_kitchen.deleted_at', NULL)
            ->join('foods', 'food_kitchen.food_id', '=', 'foods.id')
            ->where('foods.deleted_at', NULL)
            ->select(
                'foods.name as name',
                'foods.amount as amount',
                'food_kitchen.id as kitchen',
                'food_kitchen.food_id as food_id',
                'food_kitchen.kitchen_id as kitchen_id',
                'food_kitchen.status as status',
                'food_kitchen.period as period'
            )
            ->get();

        $params['roles'] =  DB::table('roles')->where('id', '!=', 1)->get();
        if ($params['login']) {
            $params['store'] = Store::where('deleted_at', NULL)->find($params['login']->store_id);
        }
        
        return $params;
    }

    public function viewUser($id)
    {
        $user = User::where('users.deleted_at', NULL)
            ->where('users.unique_id', $id)
            ->first();
        if ($user) {
            return  $user;
        }
        else{
            return ['error' => 'User not found!!!'];
        }
        
    }

    public function storeRole(Request $request, $id)
    {
        $user = User::where('deleted_at', NULL)->find($id);

        if ($user) {
            $user->role = $request->role;
            

            if ($request->role=='4') {
                $user->store= $request->store_id;

                $store = Store::where('deleted_at', NULL)->find($request->store_id);
                if ($store) {
                    $group = $store->group_bar;
                    $allusers = User::where('deleted_at', NULL)->where('store', $request->store_id)->get();
                    foreach ($allusers as $userss) {
                        if ($userss->role==4) {
                            $theuser = User::find($userss->id);
                            $theuser->role == 3;
                            $theuser->update();
                        }
                    }

                    $user->role = $request->role;
                }
            }

            $user->update();
        }
        return 'ok';
        
    }



    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|max:191|email|unique:users',
            'role' => 'required',
        ]);

        $setting = Setting::findOrFail(1);

        $name = "";
        if ($request->image) {
            $name = time().'.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ';')))[1])[1];

            \Image::make($request->image)->save(public_path('img/photos/').$name);
        }

        return User::create([
            'store' => $request['store'],
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'role' => $request['role'],
            'password' => Hash::make($request['password']),
            'next_of_kin' => $request['next_of_kin'],
            'next_of_kin_address' => $request['next_of_kin_address'],
            'next_of_kin_phone' => $request['next_of_kin_phone'],
            'image' => $name,
            'invoice'=>$request['invoice'],
            'unique_id'=> rand(9,99999).'st',

        ]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            //'bar' => 'required',   
        ]);

        $user = User::find($request->user_id);
        if ($request->bar) {
            $user->store = $request->bar;
        }
        else{
            $user->kitchen = $request->kitchen;
        }
        
        $user->update();


        $login = Login::create([
            'user_id' => auth('api')->user()->id,
            'store_id' => $request->bar,
            'kitchen_id' => $request->kitchen,
        ]);
        return 'ok';
    }

    public function logout(Request $request)
    {
        $user = User::find(auth('api')->user()->id);
        if(auth('api')->user()->role!=8) {
            $login = Login::where('user_id', auth('api')->user()->id)->where('store_id', $user->getOriginal('store'))->where('logout', NULL)->first();

            $login1 = Login::where('user_id', auth('api')->user()->id)->where('kitchen_id', $user->getOriginal('kitchen'))->where('logout', NULL)->first();
            if ($login) {
                $login->update([
                    'logout' => Carbon::now(),
                ]);
                $user->store = NULL;
                $user->update();
            }

            if ($login1) {
                $login1->update([
                    'logout' => Carbon::now(),
                ]);
            
                $user->kitchen = NULL;
                $user->update();
            }
        }

        else{
            $user->store = NULL;
            $user->kitchen = NULL;
            $user->update();
        }
        return 'ok';
    }

    public function relogin($id)
    {
        $login = Login::find($id);
        if ($login) {
            $login->logout = NULL;
            $login->update();

            $user = User::find(auth('api')->user()->id);
            $user->store = $login->store_id;
            $user->kitchen = $login->kitchen_id;
            $user->update();
        }
        return 'ok';
    }
    public function password(Request $request)
    {

        $user = User::where('deleted_at', NULL)->find(auth('api')->user()->id);

        if (!(Hash::check($request->get('current_password'), $user->password))) {
            $error = "Your current password does not matches with the password you provided. Please try again.";
            return ['error' => $error];
        }
    
        if(strlen($request->get('new_password'))<6){
            $error = "Your password must be greater than 6";
            return ['error' => $error];
           
        }

        if(strcmp($request->get('current_password'), $request->get('new_password')) == 0){
            $error = "New Password cannot be same as your current password. Please choose a different password.";
            return ['error' => $error];
           
        }


        if(!strcmp($request->get('new_password'), $request->get('password_confirmation')) == 0){
            $error = "The new password confirmation does not match.";
            return ['error' => $error];   
        }
        
        $user->update([
            'password' => Hash::make($request->get('new_password')),
        ]); 

        return ['message' => 'Success'];
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'name' => 'required|string|max:191',
            //'email' => 'required|string|max:191|unique:users,email,'.$user->id,
            //'phone' => 'required|string|max:19',
            'store' => 'required',
        ]);
   
        if ($request->password) {
            $password = Hash::make($request['password']);
        }
        else
        {
            $password = $user->password;
        }

        if ($request->image) {
            if ($request->image!=$user->image) {
                $name = $user->image;
            }
            else{
                $name = time().'.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ';')))[1])[1];

                \Image::make($request->image)->save(public_path('img/photos/').$name);
            }
        }
        else
        {
            $name = $user->image;
        }

        $user->update([
            'name' => $request['name'],
            'store' => $request['store'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'role' => $request['role'],
            'password' => $password,
            'next_of_kin' => $request['next_of_kin'],
            'next_of_kin_address' => $request['next_of_kin_address'],
            'next_of_kin_phone' => $request['next_of_kin_phone'],
            'image' => $name,
            'invoice'=>$request['invoice'],
        ]);
        return ['Message' => 'Updated'];
    }

    public function destroy($id)
    {
        User::destroy($id);
    }

    public function destroyall(Request $request)
    {
        foreach ($request->selected as $id) {
            if ($id!=1) {
                User::Destroy($id);
            }
        }
        return 'ok';
        
    }


    public function credituser(Request $request)
    {
        $user = User::findOrFail($request->payer_id);
        
        $fund = Credit::create([
            'user_id' => auth('api')->user()->id,
            'customer_id' => $request['payer_id'],
            'amount' => $request['credit_unit'],
            'prev_amount' =>  ($user->credit_unit) ? $user->credit_unit : 0,
            'ref_id' => rand(2,99999).'CU',
        ]);

        $user->update([
            'credit_unit' => $request['credit_unit'],
        ]);

        return $request;
    }

    public function walletuser(Request $request)
    {
        $this->validate($request, [
            'payer_id' => 'required',
            'amount' => 'required',
            'wallet' => 'required'
        ]);

        $fund = Fund::create([
            'user_id' => auth('api')->user()->id,
            'store_id' => auth('api')->user()->store,
            'customer_id' => $request['payer_id'],
            'amount' => $request['amount'],
            'mop' => $request['mop'],
            'account' => 4,
            'wallet' => $request['wallet'],
            'ref_id' => rand(2,99999).'FT',
            'tran_type'=> $request['tran_type'],
        ]);

        return $request;
    }

    public function rejectwallet($id)
    {
        $fund = Fund::find($id);
        if ($fund) {
            $user = User::findOrFail($request->payer_id);
            
            $fund = Fund::create([
                'status' => 2,
            ]);
        }
        return 'ok';
    }

    public function funding(Request $request)
    {
        $params = [];

        $query =  Fund::where('funds.deleted_at', NULL)->latest();


        if($request->customer_id)
        {
            $query->where('funds.customer_id', $request->customer_id);
        }

        if ($request->start_date) {
            $query->where('funds.created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('funds.created_at', '<=', $request->end_date . ' 23:59');
        }

        if ($request->selected==0) {
            $params['fundings'] =  $query->get();
        }
        else{
            $params['fundings'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();
        return $params;
    }

    public function fundreceipt($ref_id)
    {
        $fund = Fund::where('funds.deleted_at', NULL)->where('funds.ref_id', $ref_id)->first();
        return $fund;
    }

    public function approvefund($ref_id)
    {
        $fund = Fund::where('deleted_at', NULL)->where('ref_id', $ref_id)->first();
        if ($fund) {
            $fund->approved_by = auth('api')->user()->id;
            $fund->status = 2;
            $fund->update();
        
            $member = Member::findOrFail($fund->getOriginal('customer_id'));
            $user = User::where('unique_id', $member->membership_id)->first();
            
            if($fund->wallet==0) {
                $user->bar_wallet = $fund->amount + $user->bar_wallet;
            }

            elseif($fund->wallet==2) {
                $user->credit_unit = $fund->amount + $user->credit_unit;
            }
            else{
                $user->wallet_balance = $fund->amount + $user->wallet_balance;
            }
                
            $user->update();

            $last_ledger = Ledger::where('deleted_at', null)->latest()->first();
            if (!$last_ledger) {
                $ledger_id = 100000;
            }
            else{
               $ledger_id = $last_ledger->ledger_id + 100; 
            }
            $account = Account::find(33);
            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::now(),
                'user_id' => $user->id,   
                'amount' => $fund->amount,
                'debit' => $account->id,
                'credit' => $account->balancing_account->id,
                'trans_id' => $fund->ref_id,
                'description' =>  'Customer ('.$user->name.') Account Balance Funding',
            ]);
        }
        return 'ok';
    }

    public function rejectfund($ref_id)
    {
        $fund = Fund::where('deleted_at', NULL)->where('ref_id', $ref_id)->first();
        $fund->status = 0;
        $fund->update();
        return $fund;
    }

    public function createpin(Request $request)
    {
        if(!$request->password){
            return ['error' => 'Please put password'];
        }

        if(!$request->pin){
            return ['error' => 'Please put 4 digit PIN'];
        }

        if($request->pin=='1234'){
            return ['error' => 'You cannot use 1234 as PIN, try other combinations'];
        }
        $user = User::where('deleted_at', NULL)->find($request->id);

        if (!(Hash::check($request->get('password'), $user->password))) {
            return ['error' => 'Password Incorrect!!!'];
        }

        
        if ($user) {
            $user->update([
                'pin' => $request['pin'],
            ]);
            return ['Message' => 'Updated'];
        }
        else{
            return ['error' => 'User not found'];
        }
    }

    public function loadLGA($id)
    {   
        return DB::table('local_governments')->where('state_id' ,$id)->get();
    }
}
