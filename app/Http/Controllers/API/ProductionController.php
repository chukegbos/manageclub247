<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Production;
use App\Inventory;
use App\Setting;
use Illuminate\Support\Facades\Hash;
use DB;

class ProductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $query = Production::where('deleted_at', NULL)->groupBy('production_id');
        if ($request->start_date) {
            $query->where('production_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('production_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->name) {
             $query->where('production_id', 'like', '%' . $request->name . '%');
        }

        return $query->orderBy('production_date', 'Desc')->paginate(10);
    }

    public function store(Request $request)
    {
        $id = rand(9,999999);
        foreach ($request->productItems as $item) {
            $production = new Production ();
            $production->production_id = 'PROD'.$id;
            $production->product = $item['id'];
            $production->quantity = $item['quantity'];
            $production->production_date = $request->production_date;
            $saved = $production->save();

            if ($production) {
                $inventory = Inventory::find($item['id']);
                $inventory->quantity = $inventory->quantity + $item['quantity'];
                $inventory->update();
            }
        }

        return $id;
    }

    public function show($prod_id){
        $productions =  Production::where('productions.deleted_at', NULL)
            ->where('productions.production_id', $prod_id)
            ->where('inventories.deleted_at', NULL)
            ->join('inventories', 'productions.product', '=', 'inventories.id')
            ->orderBy('productions.production_date', 'Desc')
            ->select(
                'productions.id as id',
                'productions.production_id as production_id',
                'productions.quantity as quantity',
                'inventories.product_name as product_name',
                'productions.production_date as production_date'
            )
            ->get();

        return  response()->json($productions);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'quantity' => 'required',
            'total_amount' => 'required',
            'total_pay' => 'required',
            'supplier' => 'required',
            'product' => 'required',
        ]);
        $purchase = Purchase::where('deleted_at')->find($id);
        $purchase_quantity = $purchase->quantity;
        $product = Product::findOrFail($request->product);
        $product->quantity = $product->quantity - $purchase_quantity;
        $product->quantity = $product->quantity + $request->quantity;
        $product->update();

        return $purchase->update($request->all());
        //return ['message' => "Success"];
    }

    public function destroy($id)
    {
        $production = Production::where('deleted_at', NULL)->find($id);
        $production_id = $production->production_id;


        $productions = Production::where('deleted_at', NULL)->where('production_id', $production_id)->get();

        foreach ($productions as $value) {
            $product = Inventory::where('deleted_at', NULL)->find($value->product);

            if ($product->quantity >= $value->quantity) {
                $product->quantity = $product->quantity - $value->quantity;
                $product->update();

                $get_product = Production::find($value->id);
                $get_product->delete();
            }
        }

        return 'ok';
    }
}
