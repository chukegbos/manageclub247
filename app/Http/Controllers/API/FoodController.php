<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Kitchen;
use App\FoodKitchen;
use Carbon\Carbon;
use App\User;
use App\Food;
use Illuminate\Support\Facades\Hash;
use DB;

class FoodController extends Controller
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

        $query = Food::where('deleted_at', NULL);

        if ($request->name) {
            $query->where('foods.name', 'like', '%' . $request->name . '%')->orWhere('foods.code', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['foods'] =  $query->get();
        }
        else{
            $params['foods'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();

        return $params;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'amount' => 'required',
        ]);

        $code = rand(3,99888888);

        Food::create([
            'name' => ucwords($request->name),
            'amount' => $request->amount,
            'code' => $code,
        ]);

        $food = Food::where('deleted_at', NULL)->where('code', $code)->first();
        $foods = Kitchen::where('deleted_at', NULL)->get();

        foreach ($foods as $food) {
            $search_term = FoodKitchen::where('deleted_at', NULL)->where('food_id', $food->id)->where('kitchen_id', $food->id)->first();
            if (!$search_term) {
                FoodKitchen::create([
                    'food_id' => $food->id,
                    'kitchen_id' => $food->id,
                    'number' => 0,
                ]);
            }
        }

        return ['message' => "Success"];
    }

    public function update(Request $request, $id)
    {
        $food = Food::where('deleted_at')->find($id);
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'amount' => 'required',
        ]);

        $food->update([
            'name' => ucwords($request->name),
            'amount' => $request->amount,
        ]);
        return ['message' => "Success"];
    }

    public function destroy(Request $request)
    {
        foreach ($request->selected as $id) {
            Food::Destroy($id);
        }
        return 'ok';
    }

    public function getStore($code)
    {
        $food = Food::where('deleted_at', NULL)->where('code', $code)->first();
        if ($food) {
            return $food;
        }
    }


    public function show(Request $request, $code, $id)
    {
        $params = [];
        $query = FoodFood::where('inventory_store.deleted_at', NULL)->where('inventory_store.store_id', $id)
            ->join('foods', 'inventory_store.inventory_id', '=', 'foods.id')
            ->join('categories', 'foods.category', '=', 'categories.id')
            ->where('categories.deleted_at', NULL)
            ->where('foods.deleted_at', NULL);

            if ($request->orderName==0) {
                $query->orderBy('foods.product_name', 'Desc');
            }
            elseif ($request->orderName==1) {
                $query->orderBy('foods.product_name', 'asc');
            }

            if ($request->orderCategory==0) {
                $query->orderBy('categories.name', 'Desc');
            }
            elseif ($request->orderCategory==1) {
                $query->orderBy('categories.name', 'asc');
            }

            if ($request->orderAmount==0) {
                $query->orderBy('foods.price', 'Desc');
            }
            elseif ($request->orderAmount==1) {
                $query->orderBy('foods.price', 'asc');
            }

            if ($request->orderQuantity==0) {
                $query->orderBy('inventory_store.number', 'Desc');
            }
            elseif ($request->orderQuantity==1) {
                $query->orderBy('inventory_store.number', 'asc');
            }

            if ($request->orderThreshold==0) {
                $query->orderBy('foods.threshold', 'Desc');
            }
            elseif ($request->orderThreshold==1) {
                $query->orderBy('foods.threshold', 'asc');
            }

            if ($request->orderPeriod==0) {
                $query->orderBy('inventory_store.updated_at', 'Desc');
            }
            elseif ($request->orderPeriod==1) {
                $query->orderBy('inventory_store.updated_at', 'asc');
            }

            if ($request->name) {
                $query->where('foods.product_name', 'like', '%' . $request->name . '%')->orWhere('foods.product_id', 'like', '%' . $request->name . '%')->orWhere('categories.name', 'like', '%' . $request->name . '%');
            }

            //return $request;
            $query->select(
                'foods.id as id',
                'foods.category as category',
                'categories.name as name',
                'foods.product_id as product_id',
                'foods.product_name as product_name',
                'inventory_store.number as quantity',
                'inventory_store.id as room_id',
                'inventory_store.updated_at as updated_at',
                'foods.cost_price as cost_price',
                'foods.price as price',
                'foods.unit as unit',
                'foods.threshold as threshold'
            );

            if ($request->selected==0) {
                $params['foods'] =  $query->get();
            }
            else{
                $params['foods']  =  $query->paginate($request->selected);
            }
        $params['all'] = $query->count();

        return $params;
    }
}
