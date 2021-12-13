<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Process;
use App\Inventory;
use App\Setting;
use Illuminate\Support\Facades\Hash;
use DB;

class ProductionUsedController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $query = Process::where('deleted_at', NULL)->groupBy('code');
        if ($request->start_date) {
            $query->where('process_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('process_date', '<=', $request->end_date);
        }

        if ($request->name) {
             $query->where('process_id', 'like', '%' . $request->name);
        }

        return $query->orderBy('process_date', 'Desc')
        ->groupBy('code')
        ->paginate(10);
    }

    public function store(Request $request)
    {
        $id = rand(9,999999);
        foreach ($request->productItems as $item) {
            $production = new Process ();
            $production->code = 'OC'.$id;
            $production->product = $item['id'];
            $production->quantity = $item['qty'];
            $production->process_date = $request->process_date;
            $saved = $production->save();

            if ($production) {
                $inventory = Inventory::find($item['id']);
                $inventory->quantity = $inventory->quantity - $item['qty'];
                $inventory->update();
            }
        }

        return $id;
    }

    public function show($code){
        $productions =  Process::where('processes.deleted_at', NULL)
            ->where('processes.code', $code)
            ->where('inventories.deleted_at', NULL)
            ->join('inventories', 'processes.product', '=', 'inventories.id')
            ->orderBy('processes.process_date', 'Desc')
            ->select(
                'processes.id as id',
                'processes.code as code',
                'processes.quantity as quantity',
                'inventories.product_name as product_name',
                'processes.process_date as process_date'
            )
            ->get();

        return  response()->json($productions);
    }

    public function destroy($id)
    {
        $process = Process::where('deleted_at', NULL)->find($id);
        $code = $process->code;


        $processes = Process::where('deleted_at', NULL)->where('code', $code)->get();

        foreach ($processes as $value) {
            $product = Inventory::where('deleted_at', NULL)->find($value->product);

                $product->quantity = $product->quantity - $value->quantity;
                $product->update();

                $get_product = Process::find($value->id);
                $get_product->delete();
            
        }

        return 'ok';
    }
}
