<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Store;
use App\Category;
use App\FoodInventory;
use App\StockMovement;
use App\Kitchen;
use App\KitchenItem;
use Illuminate\Support\Str;

class FoodInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];
        $query =  FoodInventory::where('deleted_at', NULL);

            if ($request->name) {
                $query->where('name', 'like', '%' . $request->name . '%');
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

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'unit' => 'required',
        ]);

        $inventory = FoodInventory::create([
            'name' => ucwords($request->name),
            'amount' => $request->amount,
            'unit' =>$request->unit,
            'quantity' =>0,
        ]);


        $kitchens = Kitchen::where('deleted_at', NULL)->get();
        foreach ($kitchens as $kitchen) {
            $product = KitchenItem::where('deleted_at', NULL)
                ->where('kitchen_id', $kitchen->id)
                ->where('item', $inventory->id)
                ->first();
            
            if (!$product) {
                KitchenItem::create([
                    'item' => $inventory->id,
                    'kitchen_id' => $kitchen->id,
                    'number' => 0,
                ]);
            }
        }
        return 'ok';
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'unit' => 'required',
        ]);

        $inventory = FoodInventory::where('deleted_at', NULL)->find($id);
        $inventory->name = $request->name;
        $inventory->unit = $request->unit;
       
        if($inventory->save()){
            return ['success'=> 'Data updated successfully'];
        }else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function destroy(Request $request)
    {
        foreach ($request->selected as $id) {
            FoodInventory::Destroy($id);
        }
        return 'ok';  
    }


    public function item(Request $request, $code)
    {
        $params = [];

        $params['item'] = FoodInventory::where('deleted_at', NULL)->where('product_id', $code)->first();
        if ($params['item'] ) {
        
            $query =  KitchenItem::where('kitchen_items.deleted_at', NULL)
                ->where('kitchen_items.kitchen_id', $params['item']->id)
                ->join('stores', 'kitchen_items.kitchen_id', '=', 'stores.id')
                ->where('stores.deleted_at', NULL);
                if ($request->kitchen_id) {
                    $query->where('kitchen_items.kitchen_id', $request->kitchen_id);
                }
                $query->select(
                    'stores.name as name',
                    'kitchen_items.number as number'
                );

                if ($request->selected==0) {
                    $params['kitchens'] =  $query->get();
                }
                else{
                    $params['kitchens']  =  $query->paginate($request->selected);
                }

            $query1 =  KitchenItem::where('kitchen_items.deleted_at', NULL)
                ->where('kitchen_items.kitchen_id', $params['item']->id)
                ->join('stores', 'kitchen_items.kitchen_id', '=', 'stores.id')
                ->where('stores.deleted_at', NULL);
            if ($request->kitchen_id) {
                $query1->where('kitchen_items.kitchen_id', $request->kitchen_id);
            }
            $params['all'] = $query1->count();
            $params['total'] = $query1->sum('kitchen_items.number');
        }
        return $params;
    }
   

    public function show(Request $request, $code, $id)
    {
        
        $params = [];
        $query = KitchenItem::where('kitchen_items.deleted_at', NULL)
            ->where('kitchen_items.kitchen_id', $id)
            ->join('food_inventory', 'kitchen_items.item', '=', 'food_inventory.id')
            ->where('food_inventory.deleted_at', NULL);

            if ($request->name) {
                $query->where('food_inventory.name', 'like', '%' . $request->name . '%');
            }

            //return $request;
            $query->select(
                'kitchen_items.id as id',
                'food_inventory.name as name',
                'kitchen_items.number as quantity',
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


    public function requestinitiate(Request $request, $kitchen_id)
    {
        $ref_id = rand(1, 888888);

        foreach ($request->selected as $id) {
            $kitchen = KitchenItem::where('deleted_at', NULL)->find($id); 
            $product = FoodInventory::where('deleted_at', NULL)->find($kitchen->item);  
            if ($product) {
                $movement = new StockMovement ();
                $movement->ref_id = $ref_id;
                $movement->kitchen_id = $kitchen_id;
                $movement->user_id = auth('api')->user()->id;
                $movement->product_id = $kitchen->item;
                $movement->save(); 
            }
        }
        return $ref_id;
    }

    public function updatereq(Request $request)
    {
        foreach ($request->productItems as $item) {
            $product = StockMovement::find($item['id']);
            $product->quantity = $item['quantity'];
            $product->status = 'requested';
            $product->update();
        }
        return $request;
    }

    public function getmovements($ref_id)
    {
        $params = [];
        $params['products'] = StockMovement::where('deleted_at', NULL)
            ->where('ref_id', $ref_id)->get();
        return $params;
    }

    public function movements(Request $request)
    {
        $user = auth('api')->user();
        $params = [];

        $query = StockMovement::where('stock_movement.deleted_at', NULL)
            ->where('stock_movement.status', '!=', 'unapproved')
            ->join('food_inventory', 'stock_movement.product_id', '=', 'food_inventory.id')
            ->where('food_inventory.deleted_at', NULL)
            ->join('kitchens', 'stock_movement.kitchen_id', '=', 'kitchens.id')
            ->where('kitchens.deleted_at', NULL)
            ->orderBy('stock_movement.created_at', 'desc')
            ->select(
                'stock_movement.id as id',
                'stock_movement.product_id as product_id',
                'stock_movement.quantity as quantity',
                'stock_movement.title as title',
                'stock_movement.status as status',
                'stock_movement.user_id as user_id',
                'stock_movement.available as available',
                'stock_movement.approved_by as approved_by',
                'stock_movement.manager_id as manager_id',
                'kitchens.name as store_name'
            );

            if ($request->kitchen_id) {
                $query->where('stock_movement.kitchen_id', $request->kitchen_id);
            }
        
            if ($request->status) {
                $query->where('stock_movement.status', $request->status);
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

        $query = StockMovement::where('deleted_at', NULL)
            ->where('status', '!=', 'unapproved')
            ->where('kitchen_id', $user->getOriginal('kitchen'))->latest();

            if ($request->kitchen_id) {
                $query->where('kitchen_id', $request->kitchen_id);
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
    

    public function outletacceptall(Request $request)
    {
        //return $request->selected;
        foreach ($request->selected as $item) {
            $room = StockMovement::find($item);
            $sending = FoodInventory::where('deleted_at', NULL)->find($room->product_id);
            if ($room->status == 'pending approval' || $room->status == 'requested') {
                if ($sending->quantity >= $room->quantity) {
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
            $room = StockMovement::find($id);
            if ($room->status == 'pending approval' || $room->status == 'requested') {
                $room->approved_by = auth('api')->user()->id;
                $room->status = 'not approved';
                $room->update();
            }
        }
        return 'ok';
    }

    public function sacceptall(Request $request)
    {
        foreach ($request->selected as $id) {
            $room = StockMovement::find($id);
            if ($room->status == 'approved') {
                $inventory = FoodInventory::where('deleted_at', NULL)->find($room->product_id);
                $inventory->quantity = $inventory->quantity - $room->quantity;
                $sure = $inventory->update();

                $kitchen = KitchenItem::find($room->kitchen_id);
                $kitchen->number = $kitchen->number + $room->quantity;
                $sure1 = $kitchen->update();

                if ($sure && $sure1) {
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
            
            $room = StockMovement::find($id);
            if ($room->status == 'approved') {
                $room->manager_id = auth('api')->user()->id;
                $room->status = 'rejected';
                $room->update();
            }
        }
        return 'ok';
    }
}
