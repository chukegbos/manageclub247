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
use App\Inventory;
use App\InventoryStore;
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
        //return carbon::now();
        /*$ledger = Ledger::where('deleted_at', NULL)->where('ledger_date', carbon::now())->where('account', 6)->first();

        if (!$ledger) {

            $last_ledger = Ledger::where('deleted_at', null)->latest()->first();

            if (!$last_ledger) {
                $ledger_id = 100000;
            }
            else{
               $ledger_id = $last_ledger->ledger_id + 100; 
            }
          
            $account = Account::where('deleted_at', NULL)->find(6);
            $type = $account->type;
            
            $account_type = AccountType::where('deleted_at', NULL)->find($type);

            $setting = Setting::find(1);

            if ($account_type->type == 'Credit') {
                $debit = 0;
                $credit = $setting->working_capital;
            }
            else
            {
                $debit = $setting->working_capital;
                $credit = 0;
            }

            Ledger::create([
                'ledger_id' => $ledger_id,
                'ledger_date' => Carbon::now(),
                'account' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $account->description,
            ]);

            //closing
            $last2_ledger = Ledger::where('deleted_at', null)->latest()->first();

            if (!$last2_ledger) {
                $ledger_id2 = 100000;
            }
            else{
               $ledger_id2 = $last2_ledger->ledger_id + 100; 
            }
          
            $account2 = Account::where('deleted_at', NULL)->find(7);
            $type2 = $account2->type;
            
            $account2_type = AccountType::where('deleted_at', NULL)->find($type2);

           

            if ($account2_type->type == 'Credit') {
                $debit2 = 0;
                $credit2 = $setting->working_capital;
            }
            else
            {
                $debit2 = $setting->working_capital;
                $credit2 = 0;
            }

            Ledger::create([
                'ledger_id' => $ledger_id2,
                'ledger_date' => Carbon::now()->subDays(1),
                'account' => $account2->id,
                'debit' => $debit2,
                'credit' => $credit2,
                'description' => $account2->description,
            ]);
        }*/

        $params = [];
        $params['customers'] = User::where('deleted_at', NULL)->where('role', 0)->get();
        $params['user'] = auth('api')->user();
        //$params['user_store'] = $params['user']->getOriginal('store');

        $params['accounts'] = Account::where('deleted_at', NULL)->get();
        $params['types'] = AccountType::where('deleted_at', NULL)->get();
        $params['products'] = Inventory::where('deleted_at', NULL)->get();
        $params['stores'] = Store::where('deleted_at', NULL)->where('id', '!=', 1)->get();
        $params['suppliers'] = Supplier::where('deleted_at', NULL)->get();
        $params['login'] = Login::where('user_id', auth('api')->user()->id)->where('logout', '!=', NULL)->where('verified_by', NULL)->first();
        $params['inventory'] = InventoryStore::where('inventory_store.deleted_at', NULL)
            ->where('inventory_store.store_id',  auth()->user()->getOriginal('store'))
            ->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
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
            ->join('stores', 'users.store', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->select(
                'stores.name as store_name',
                'users.name as name',
                'users.phone as phone',
                'users.email as email',
                'users.role as role',
                'users.address as address',
                'users.next_of_kin as next_of_kin',
                'users.next_of_kin_address as next_of_kin_address',
                'users.next_of_kin_phone as next_of_kin_phone',
                'users.invoice as invoice'
            )
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

        if ($request['role']==2) {
            $sale_percent = $setting->cashier_percent;
        }
        elseif ($request['role']==4) {
            $sale_percent = $setting->manager_percent;
        }

        elseif ($request['role']==3) {
            $sale_percent = $setting->admin_percent;
        }
        else{
            $sale_percent = 0;
        }

        return User::create([
            'salary' => $request['salary'],
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'sale_percent' => $sale_percent,
            'role' => $request['role'],
            'store' => $request['store'],
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
            'bar' => 'required',   
        ]);

        $user = User::find($request->user_id);
        $user->store = $request->bar;
        $user->update();


        $login = Login::create([
            'user_id' => auth('api')->user()->id,
            'store_id' => $request->bar,
        ]);
        return 'ok';
    }

    public function logout(Request $request)
    {
        $user = User::find(auth('api')->user()->id);
        if(auth('api')->user()->role!=8) {
            $login = Login::where('user_id', auth('api')->user()->id)->where('store_id', $user->getOriginal('store'))->where('logout', NULL)->first();
            if ($login) {
                $login->update([
                    'logout' => Carbon::now(),
                ]);
                
                $user->store = NULL;
                $user->update();
            }
        }

        else{
            $user->store = NULL;
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
            'email' => 'required|string|max:191|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:19',
            'address' => 'required|string|max:191',
            'store' => 'required',
            'next_of_kin' => 'required|string|max:191',
            'next_of_kin_address' => 'required|string|max:191',
            'next_of_kin_phone' => 'required|string|max:19'
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

         if ($request['role']==2) {
            $sale_percent = $setting->cashier_percent;
        }
        elseif ($request['role']==4) {
            $sale_percent = $setting->manager_percent;
        }

        elseif ($request['role']==3) {
            $sale_percent = $setting->admin_percent;
        }
        else{
            $sale_percent = 0;
        }

        $user->update([
            'name' => $request['name'],
            'salary' => $request['salary'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'role' => $request['role'],
            'store' => $request['store'],
            'sale_percent' => $sale_percent,
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
        
        if ((!$request->payer_id) || (!$request->amount) || (!$request->mop )) {
            return ['error' => 'Please fill all the fields!!!'];
        }

        $fund = Fund::create([
            'user_id' => auth('api')->user()->id,
            'store_id' => auth('api')->user()->store,
            'customer_id' => $request['payer_id'],
            'amount' => $request['amount'],
            'mop' => $request['mop'],
            'account' => 4,
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

        $query =  Fund::where('funds.deleted_at', NULL);

        if ($request->orderName==0) {
            $query->orderBy('funds.customer_id', 'Desc');
        }
        elseif ($request->orderName==1) {
            $query->orderBy('funds.customer_id', 'asc');
        }

        if ($request->orderDate==0) {
            $query->orderBy('funds.created_at', 'Desc');
        }
        elseif ($request->orderDate==1) {
            $query->orderBy('funds.created_at', 'asc');
        }



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
        $query->select(
            'funds.id as id',
            'funds.user_id as user_id',
            'funds.ref_id as ref_id',
            'funds.customer_id as customer_id',
            'funds.tran_type as tran_type',
            'funds.approved_by as approved_by',
            'funds.status as status',
            'funds.mop as mop',
            'funds.status as status',
            'funds.amount as amount',
            'funds.created_at as created_at'
        );

        if ($request->selected==0) {
            $params['fundings'] =  $query->get();
        }
        else{
            $params['fundings'] =  $query->paginate($request->selected);
        }
        


        $query1 =  Fund::where('funds.deleted_at', NULL)
            ->join('users', 'funds.customer_id', '=', 'users.id')
            ->where('users.deleted_at', NULL);

        if($request->customer_id)
        {
            $query1->where('funds.customer_id', $request->customer_id);
        }

        if ($request->start_date) {
            $query1->where('funds.created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query1->where('funds.created_at', '<=', $request->end_date . ' 23:59');
        }

        $params['all'] = $query1->count();
        return $params;
    }

    public function fundreceipt($ref_id)
    {
        $fund = Fund::where('funds.deleted_at', NULL)
            ->join('stores', 'funds.store_id', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->where('funds.ref_id', $ref_id)
            ->select(
                'stores.name as store_name',
                'stores.phone as store_phone',
                'stores.email as store_email',
                'stores.address as store_address',
                'funds.id as id',
                'funds.user_id as user_id',
                'funds.ref_id as ref_id',
                'funds.customer_id as customer_id',
                'funds.tran_type as tran_type',
                'funds.approved_by as approved_by',
                'funds.status as status',
                'funds.mop as mop',
                'funds.status as status',
                'funds.amount as amount',
                'funds.created_at as created_at'
            )
            ->first();
        return $fund;
    }

    public function approvefund($ref_id)
    {
        $fund = Fund::where('deleted_at', NULL)->where('ref_id', $ref_id)->first();
        if ($fund) {
            $fund->approved_by = auth('api')->user()->id;
            $fund->status = 2;
            $fund->update();
        
            $user = User::findOrFail($fund->getOriginal('customer_id'));
           
            $user->update([
                'wallet_balance' => $fund->amount + $user->wallet_balance,
            ]);

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
