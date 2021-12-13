<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use App\Inventory;
use App\InventoryStore;
use Illuminate\Support\Str;

class InventoryController extends Controller
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
        $params['categories'] = Category::where('deleted_at', NULL)->get();
        $query =  Inventory::where('inventories.deleted_at', NULL)
            ->where('inventories.deleted_at', NULL)
            ->join('categories', 'inventories.category', '=', 'categories.id')
            ->where('categories.deleted_at', NULL);

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
                $query->orderBy('inventories.quantity', 'Desc');
            }
            elseif ($request->orderQuantity==1) {
                $query->orderBy('inventories.quantity', 'asc');
            }

            if ($request->orderCost==0) {
                $query->orderBy('inventories.cost_price', 'Desc');
            }
            elseif ($request->orderCost==1) {
                $query->orderBy('inventories.cost_price', 'asc');
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
                'inventories.quantity as quantity',
                'inventories.cost_price as cost_price',
                'inventories.number_per_crate as number_per_crate',
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


        $qy = InventoryStore::where('inventory_store.deleted_at', NULL)
            ->where('inventory_store.store_id', auth('api')->user()->getOriginal('store'))
            ->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->join('categories', 'inventories.category', '=', 'categories.id')
            ->where('categories.deleted_at', NULL)
            ->where('inventories.deleted_at', NULL);

            if ($request->orderName==0) {
                $qy->orderBy('inventories.product_name', 'Desc');
            }
            elseif ($request->orderName==1) {
                $qy->orderBy('inventories.product_name', 'asc');
            }

            if ($request->orderCategory==0) {
                $qy->orderBy('categories.name', 'Desc');
            }
            elseif ($request->orderCategory==1) {
                $qy->orderBy('categories.name', 'asc');
            }

            if ($request->orderAmount==0) {
                $qy->orderBy('inventories.price', 'Desc');
            }
            elseif ($request->orderAmount==1) {
                $qy->orderBy('inventories.price', 'asc');
            }

            if ($request->orderQuantity==0) {
                $qy->orderBy('inventory_store.number', 'Desc');
            }
            elseif ($request->orderQuantity==1) {
                $qy->orderBy('inventory_store.number', 'asc');
            }

            if ($request->orderThreshold==0) {
                $qy->orderBy('inventories.threshold', 'Desc');
            }
            elseif ($request->orderThreshold==1) {
                $qy->orderBy('inventories.threshold', 'asc');
            }

            if ($request->orderPeriod==0) {
                $qy->orderBy('inventory_store.updated_at', 'Desc');
            }
            elseif ($request->orderPeriod==1) {
                $qy->orderBy('inventory_store.updated_at', 'asc');
            }

            if ($request->name) {
                $qy->where('inventories.product_name', 'like', '%' . $request->name . '%')->orWhere('inventories.product_id', 'like', '%' . $request->name . '%')->orWhere('categories.name', 'like', '%' . $request->name . '%');
            }

            //return $request;
            $qy->select(
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
                $params['invt'] =  $qy->get();
            }
            else{
                $params['invt']  =  $qy->paginate(20);
            }
        $params['all_inventory'] = $qy->count();
        return $params;
    }

    public function item(Request $request, $code)
    {
        $params = [];

        $params['item'] = Inventory::where('deleted_at', NULL)->where('product_id', $code)->first();
        if ($params['item'] ) {
        
            $query =  InventoryStore::where('inventory_store.deleted_at', NULL)
                ->where('inventory_store.inventory_id', $params['item']->id)
                ->join('stores', 'inventory_store.store_id', '=', 'stores.id')
                ->where('stores.deleted_at', NULL);
                if ($request->store_id) {
                    $query->where('inventory_store.store_id', $request->store_id);
                }
                $query->select(
                    'stores.name as name',
                    'inventory_store.number as number'
                );

                if ($request->selected==0) {
                    $params['inventories'] =  $query->get();
                }
                else{
                    $params['inventories']  =  $query->paginate($request->selected);
                }

            $query1 =  InventoryStore::where('inventory_store.deleted_at', NULL)
                ->where('inventory_store.inventory_id', $params['item']->id)
                ->join('stores', 'inventory_store.store_id', '=', 'stores.id')
                ->where('stores.deleted_at', NULL);
            if ($request->store_id) {
                $query1->where('inventory_store.store_id', $request->store_id);
            }
            $params['all'] = $query1->count();
            $params['total'] = $query1->sum('inventory_store.number');
        }
        return $params;
    }
    public function getInventory(Request $request)
    {
        $inventory = Inventory::where('deleted_at', NULL)->pluck('id', 'product_name')->toArray();
        return $inventory;
    }
 
    public function store(Request $request)
    {
        $this->validate($request, [
            'product_name' => 'required|string|max:255',
            'price' => 'required|integer',
            'cost_price' => 'required|integer',
            'number_per_crate' => 'required|integer',
            'category' => 'required|integer',
        ]);

        $productId = 'FO'. rand();

        return Inventory::create([
            'product_id' => $productId,
            'product_name' => ucwords($request->product_name),
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'category' => $request->category,
            'unit' =>$request->unit,
            'number_per_crate' => $request->number_per_crate,
        ]);
    }

    public function increase(Request $request)
    {
        $this->validate($request, [
            'number' => 'required|string|max:255'
        ]);

        $inventories = Inventory::where('deleted_at', NULL)->where('category', $request->category)->get();

        foreach ($inventories as $inventory) {
            $product = Inventory::where('deleted_at', NULL)->find($inventory->id);
            $newPrice = (($request->number/100) * $product->price) + $product->price;
            $product->price = $newPrice;
            $product->update();
        }
        return $inventories;
    }

    public function show($id)
    {
        $inventory =  Inventory::where('deleted_at', NULL)->find($id);
        return response()->json($inventory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'product_name' => 'required|string|max:255',
            'price' => 'required|integer',
            'cost_price' => 'required|integer',
            'number_per_crate' => 'required|integer',
        ]);

        $inventory = Inventory::where('deleted_at', NULL)->find($id);
        $inventory->product_name = $request->product_name;
        $inventory->price = $request->price;
        $inventory->cost_price = $request->cost_price;
        $inventory->category = $request->category;
        $inventory->unit = $request->unit;
        $inventory->number_per_crate = $request->number_per_crate;
       
        if($inventory->save()){
            return ['success'=> 'Data updated successfully'];
        }else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function reset(Request $request)
    {
        $inventories = Inventory::where('deleted_at', NULL)->get();
        $stores = InventoryStore::where('deleted_at', NULL)->get();
        foreach ($inventories as $inventory) {
            $product = Inventory::find($inventory->id);
            $product->quantity = 0;
            $product->update();
        }

        foreach ($stores as $store) {
            $product = InventoryStore::find($store->id);
            $product->number = 0;
            $product->update();
        }
        return $inventories;
    }

    public function storereset(Request $request)
    {
        $stores = InventoryStore::where('deleted_at', NULL)->where('store_id', auth('api')->user()->getOriginal('store'))->get();

        foreach ($stores as $store) {
            $product = InventoryStore::find($store->id);
            $product->number = 0;
            $product->update();
        }
        return $store;
    }
    
    public function destroy(Request $request)
    {
        foreach ($request->selected as $id) {
            //$getProduct = json_decode($product);
            //$the_product = Inventory::find($id);
            Inventory::Destroy($id);
        }
        return 'ok';
        
    }

    /** Support functions  */

    public function loadQuantity($id){
        $inventory = Inventory::where('id', $id)->first();

        return response()->json($inventory->quantity);
    }


    public function addQuantity(Request $request, $id){
        $inventory = Inventory::where('id', $id)->first();
        $this->validate($request, [
            'quantity' => 'required|integer'
        ]);

         $inventory->update([
            'quantity' => $request->quantity
        ]);
        return ['new_quantity' => $inventory->quantity];
    }

    public function subtractQuantity(Request $request, $id){
        $inventory = Inventory::where('id', $id)->first();
        $this->validate($request, [
            'quantity' => 'required|integer'
        ]);

         $inventory->update([
            'quantity' => $request->quantity
        ]);
        return ['new_quantity' => $inventory->quantity];
    }
}
