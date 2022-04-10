<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Store;
use App\Kitchen;
use App\StoreRequest;
use App\InventoryStore;
use App\Sale;
use App\Item;
use App\Debtor;
use App\PaymentDebit;
use App\Member;
use App\DebtorHistory;
use Carbon\Carbon;
use App\User;
use App\Room;
use App\Ledger;
use App\Inventory;
use App\RoomMovement;
use App\StoreUser;
use App\Account;
use App\AccountType;
use Illuminate\Support\Facades\Hash;
use DB;

class StoreController extends Controller
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
        $query = Store::where('deleted_at', NULL)->where('id', '!=', 1);

        if ($request->orderName==0) {
            $query->orderBy('name', 'Desc');
        }
        elseif ($request->orderName==1) {
            $query->orderBy('name', 'asc');
        }

        if ($request->orderEmail==0) {
            $query->orderBy('email', 'Desc');
        }
        elseif ($request->orderEmail==1) {
            $query->orderBy('email', 'asc');
        }

        if ($request->orderTarget==0) {
            $query->orderBy('target', 'Desc');
        }
        elseif ($request->orderTarget==1) {
            $query->orderBy('target', 'asc');
        }

        if ($request->orderLimit==0) {
            $query->orderBy('stock_limit', 'Desc');
        }
        elseif ($request->orderTarget==1) {
            $query->orderBy('stock_limit', 'asc');
        }



        if ($request->name) {
            $query->where('stores.name', 'like', '%' . $request->name . '%')->orWhere('stores.code', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['stores'] =  $query->get();
        }
        else{
            $params['stores'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();

        return $params;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            /*'group_bar' => 'required',*/
        ]);

        $code = rand(3,99888888);

        $store = Store::create([
            'name' => ucwords($request->name),
            //'group_bar' => $request->group_bar,
            'code' => $code,
        ]);

        $inventories = Inventory::where('deleted_at', NULL)->get();

        foreach ($inventories as $inventory) {
            $search_term = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $inventory->id)->where('store_id', $store->id)->first();

            if (!$search_term) {
                InventoryStore::create([
                    'inventory_id' => $inventory->id,
                    'store_id' => $store->id,
                    'number' => 0,
                ]);
            }
        }

        return ['message' => "Success"];
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
        $params['all'] = $query->count();

        return $params;
    }

    public function updatedrink(Request $request, $id)
    {
        $drink = InventoryStore::where('deleted_at', NULL)->find($id);
        if ($drink) {
            $drink->number = $request->quantity;
            $drink->update();
        }

        return 'Ok';
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
            ->where('rooom_movement.moved', 1)
            ->where('rooom_movement.status', '!=', 'unapproved')
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
                'rooom_movement.main_product as main_product',
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

        $params['all'] = $query->count();

        return $params;
    }

    public function myrequest(Request $request)
    {
        $user = auth('api')->user();
        $params = [];

        $query = RoomMovement::where('deleted_at', NULL)
            ->where('store_id', $user->getOriginal('store'))->latest();

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
        $params['all'] = $query->count();

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
            $each_price = $product['qty'] * $product['price'];
            array_push($get_price, $each_price);
        }

        $get_amount = array();
        foreach ($request->serviceItems as $service) {
            $each_amount = $service['qty'] * $service['amount'];
            array_push($get_amount, $each_amount);
        }
      
        return (array_sum($get_price) + array_sum($get_amount));
    }

    public function getnumber(Request $request)
    {
        $item = Inventory::where('deleted_at', NULL)->where('product_id', $request->inventory)->first();
        if ($item) {
            $inventory = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $item->id)->where('store_id', auth('api')->user()->getOriginal('store'))->first();
            return $inventory->number;
        }
        else{
            $inventory = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $request->inventory)->where('store_id', auth('api')->user()->getOriginal('store'))->first();
            if($inventory) {
                return $inventory->number;
            }
            else {
                $inventory = NULL;
            }
        }
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


    public function update(Request $request, $id)
    {
        $bar = Store::where('deleted_at')->find($id);
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $bar->update([
            'group_bar' => $request->group_bar,
            'target' => $request->target,
            'phone' => $request->phone,
            'email' => $request->email,
            'stock_limit' => $request->stock_limit,
            'name' => ucwords($request->name),
        ]);
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
            $movement->store_id = auth('api')->user()->getOriginal('store');
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
            $product->moved = 1;
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
                $inventory = InventoryStore::where('deleted_at', NULL)->find($room->product_id);
                $product = Inventory::find($inventory->inventory_id);

                $inventory->number = $inventory->number + ($room->qty * $product->number_per_crate);
                $sure = $inventory->update();
                if ($sure) {
                    $room->status = 'accepted';
                    $room->manager_id = auth('api')->user()->id;
                    $room->update();
                }
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
        //return $request->selected;
        foreach ($request->selected as $item) {
            $room = RoomMovement::find($item);

            $recieve = InventoryStore::where('deleted_at', NULL)->find($room->product_id);
            $product = $recieve->inventory_id;
            $inv = Inventory::find($product);

            $sending = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $product)->where('store_id', 1)->first();
            

            if ($room->status == 'pending approval' || $room->status == 'requested') {    
                /*if ($sending->number>=$room->qty) {
                    $sending->number = $sending->number - $room->qty;
                }*/

                $mainproduct = $sending->number/$inv->number_per_crate;

                if ($mainproduct >= $room->qty) {
                    $sending->number = $sending->number - ($room->qty * $inv->number_per_crate);
                    $sending->update();

                    //$recieve->number = $recieve->number + ($room->qty * $inv->number_per_crate);
                    //$recieve->update();

                    $room->status = 'approved';
                    $room->approved_by = auth('api')->user()->id;
                    $room->update();
                }
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

        $query = Sale::where('deleted_at', NULL)->where('status', '!=', 'pending')->latest();

         if (auth('api')->user()->role==14) {
            $query->where('sec_id', '!=', NULL);
        }
        else{
            $query->where('store_id', '!=', NULL);
        }

        /*if (auth('api')->user()->role==7 || auth('api')->user()->role==8) {
            $query->where('store_id',  auth('api')->user()->getOriginal('store'));
        }*/

        //Optional where
        if ($request->frontdesk_id) {
            $query->where('cashier_id', $request->frontdesk_id);
        }

        if ($request->steward_id) {
            $query->where('market_id', $request->steward_id);
        }

        if ($request->start_date) {
            $query->where('main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->staff) {
            $query->where('user_id', $request->staff);
        }

        if ($request->buyer) {
            $query->where('buyer', $request->buyer);
        }

        if ($request->kitchen) {
            $query->where('sec_id', $request->kitchen);
        }

        if ($request->store) {
            $query->where('store_id', $request->store);
        }

        if ($request->customer) {
            $query->where('name', 'like', '%' . $request->customer . '%');
        }

        if ($request->selected==0) {
            $params['report_data'] =  $query->get();
        }
        else{
            $params['report_data']  =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();
        $params['total_amount'] = $query->sum('totalPrice');
        $params['users'] = User::where('deleted_at', NULL)->where('role', '!=', 0)->get();

        return $params;
    }

    public function item(Request $request)
    {
        $params = [];

        $query = Item::where('items.deleted_at', NULL)
        ->join('sales', 'items.code', '=', 'sales.sale_id')
        ->where('sales.status', 'concluded')
        ->where('sales.deleted_at', NULL)
        ->orderBy('items.qty', 'desc');

        //Optional where
            
        if ($request->frontdesk_id==0) {
            $params['frontdesk'] = "All";
        }
        else{
            $query->where('sales.cashier_id', $request->frontdesk_id);
            $fdo = User::where('deleted_at', NULL)->find($request->frontdesk_id);
            $params['frontdesk'] = $fdo->name;
        }
    
        if ($request->steward_id==0) {
            $params['steward'] = "All";
        }
        else{
            $query->where('sales.market_id', $request->steward_id);
            $stw = User::where('deleted_at', NULL)->find($request->steward_id);
            $params['steward'] = $stw->name;
        }
        
        if ($request->kitchen_id==0) {
            $params['kitchen'] = "All";
        }
        else {
            $query->where('sales.sec_id', $request->kitchen_id);
            $kitchen = Kitchen::where('deleted_at', NULL)->find($request->kitchen_id);
            $params['kitchen'] = $kitchen->name;
        }
        
        if ($request->store_id==0) {
            $params['store'] = "All";
        }
        else {
            $query->where('sales.store_id', $request->store_id);
            $store = Store::where('deleted_at', NULL)->find($request->store_id);
            $params['store'] = $store->name;
        }

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
            $query->where('sales.name', 'like', '%' . $request->customer . '%');
        }

        $query->select(DB::raw("
            sales.cashier_id as cashier_id, 
            sales.market_id as market_id, 
            sales.main_date as main_date, 
            sales.user_id as user_id, 
            sales.buyer as buyer, 
            sales.sec_id as sec_id,
            sales.store_id as store_id,  
            items.price as price, 
            items.qty as qty, 
            SUM(items.qty) As qty, 
            SUM(items.price * items.qty) As totalPrice, 
            items.product_id as product_id, 
            items.product_name as product_name"
        ));

        /*if (auth('api')->user()->role==14) ->groupBy('product_id'){
            $query->where('sec_id', '!=', NULL);
        }
        else{
            $query->where('store_id', '!=', NULL);
        }

        if (auth('api')->user()->role==7 || auth('api')->user()->role==8) {
            $query->where('store_id',  auth('api')->user()->getOriginal('store'));
        }*/

        
        if ($request->selected==0) {
            $params['report_data'] = $query->groupBy('items.product_id')->get();
        }
        else{
            $params['report_data'] = $query->groupBy('items.product_id')->paginate($request->selected);
        }
        $params['stores'] = Store::where('deleted_at', NULL)->where('id', '!=', 1)->get();
        $params['kitchens'] = Kitchen::where('deleted_at', NULL)->get();
        $params['users'] = User::where('deleted_at', NULL)->where('role', '!=', 0)->get();
        return $params;
    }

    public function quotes(Request $request)
    {
        $params = [];

        $query = Sale::where('sales.deleted_at', NULL);
        if (auth('api')->user()->role==14) {
            $query->where('sec_id', '!=', NULL);
        }
        else{
            $query->where('store_id', '!=', NULL);
        }
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

        $query->where('sales.status', '=', 'pending')->orderBy('sales.main_date', 'Desc')
            ->select(
                'sales.id as id',
                'sales.sale_id as sale_id',
                
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
         
        $params['all'] = $query->count();
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
        set_time_limit(0);
        $get_people = User::where('deleted_at', NULL)->where('role', 0)->get();
        foreach ($get_people as $people) {
            $person = User::find($people->id);
            $unique_id = $person->unique_id;
            if ($unique_id) {
                $member = Member::where('membership_id', $unique_id)->first();
                if ($member) {
                    $debt = PaymentDebit::where('deleted_at', NULL)->where('member_id', $member->id)->where('status', 0)->sum('amount');
                    $person->debt = $debt;
                    $person->update();
                }

            }
        }

        $query = User::where('users.deleted_at', NULL)
            ->where('default_esc_members.deleted_at', NULL)
            ->where('users.role', 0)
            ->join('default_esc_members', 'users.unique_id', '=', 'default_esc_members.membership_id')
            ->join('default_esc_payment_debits', 'default_esc_members.id', '=', 'default_esc_payment_debits.member_id')
            ->where('default_esc_payment_debits.status', 0)
            ->groupBy('default_esc_payment_debits.member_id');
        

        if ($request->end_date) {
            $query->where('default_esc_payment_debits.date_entered', '<=', $request->end_date);
        }

        if ($request->min_amount) {
            $query->where('users.debt', '>=', $request->min_amount);
        }

        if ($request->max_amount) {
            $query->where('users.debt', '<=', $request->max_amount);
        }
        //$query->whereBetween('users.debt', [$request->min_amount, $request->max_amount]);

        $query->select(
            'users.id as id',
            'users.unique_id as unique_id',
            'users.name as name',
            'users.email as email',
            'users.debt as debt',
            'default_esc_payment_debits.date_added as date_added',
            'default_esc_payment_debits.date_entered as date_entered',
            'users.phone as phone'
        )->orderBy('users.name', 'asc');

        $params['allusers'] = $query->get();
        
        if ($request->selected==0) {
            $params['customers'] =  $query->get();
        }
        else{
            $params['customers'] = $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();
        return $params;
    }

    public function notowing(Request $request)
    {
        $params = [];
        set_time_limit(0);
        $get_people = User::where('deleted_at', NULL)->where('role', 0)->get();
        foreach ($get_people as $people) {
            $person = User::find($people->id);
            $unique_id = $person->unique_id;
            if ($unique_id) {
                $member = Member::where('membership_id', $unique_id)->first();
                if ($member) {
                    $debt = PaymentDebit::where('deleted_at', NULL)->where('member_id', $member->id)->where('status', 0)->sum('amount');
                    $person->debt = $debt;
                    $person->update();
                }

            }
        }

        $query = User::where('users.deleted_at', NULL)
            ->where('default_esc_members.deleted_at', NULL)
            ->where('users.role', 0)
            ->where('users.debt', 0)
            ->join('default_esc_members', 'users.unique_id', '=', 'default_esc_members.membership_id');
        

        //$query->whereBetween('users.debt', [$request->min_amount, $request->max_amount]);

        $query->select(
            'users.id as id',
            'users.unique_id as unique_id',
            'users.name as name',
            'users.email as email',
            'users.debt as debt',
            'default_esc_members.phone_1 as phone'
        )->orderBy('users.name', 'asc');

        $params['allusers'] = $query->get();
        
        if ($request->selected==0) {
            $params['customers'] =  $query->get();
        }
        else{
            $params['customers'] = $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();
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
}
