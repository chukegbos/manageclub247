<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Kitchen;
use App\FoodKitchen;
use App\ServiceItem;
use App\Production;
use Carbon\Carbon;
use App\User;
use App\Food;
use Illuminate\Support\Facades\Hash;
use DB;

class KitchenController extends Controller
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
        $query = Kitchen::where('deleted_at', NULL);

        if ($request->name) {
            $query->where('kitchens.name', 'like', '%' . $request->name . '%')->orWhere('kitchens.code', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['kitchens'] =  $query->get();
        }
        else{
            $params['kitchens'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();

        return $params;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $code = rand(3,99888888);

        Kitchen::create([
            'name' => ucwords($request->name),
            'code' => $code,
        ]);

        $kitchen = Kitchen::where('deleted_at', NULL)->where('code', $code)->first();
        $foods = Food::where('deleted_at', NULL)->get();

        foreach ($foods as $food) {
            $search_term = FoodKitchen::where('deleted_at', NULL)->where('food_id', $food->id)->where('kitchen_id', $kitchen->id)->first();
            if (!$search_term) {
                FoodKitchen::create([
                    'food_id' => $food->id,
                    'kitchen_id' => $kitchen->id,
                    'number' => 0,
                ]);
            }
        }

        return ['message' => "Success"];
    }

    public function update(Request $request, $id)
    {
        $kitchen = Kitchen::where('deleted_at')->find($id);
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $kitchen->update([
            'name' => ucwords($request->name),
        ]);
        return ['message' => "Success"];
    }

    public function updatedish(Request $request, $id)
    {
        $kitchen = FoodKitchen::where('deleted_at')->find($id);
        $this->validate($request, [
            'number' => 'required',
        ]);

        $kitchen->update([
            'number' => $kitchen->number + $request->number,
        ]);

        Production::create([
            'production_date' => Carbon::today(),
            'product' => $kitchen->id,
            'quantity' => $request->number,
        ]);

        return ['message' => "Success"];
    }

    public function destroy(Request $request)
    {
       foreach ($request->selected as $id) {
            Kitchen::Destroy($id);
        }
        return 'ok';
    }

    public function viewkitchen($code)
    {
        $kitchen = Kitchen::where('deleted_at', NULL)->where('code', $code)->first();
        if ($kitchen) {
            return $kitchen;
        }
    }

    public function service(Request $request)
    {
        $query = ServiceItem::where('deleted_at', NULL)->where('paid', 1);

        if (auth('api')->user()->role==14 || auth('api')->user()->role==15) {
            $kitchen_id = auth('api')->user()->getOriginal('kitchen');

            $query->where('main_kitchen', $kitchen_id);
        }

        if ($request->selected==0) {
            $params['services'] =  $query->get();
        }
        else{
            $params['services'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();

        return $params;
    }

    public function pendingservice(Request $request)
    {
        $query = ServiceItem::where('deleted_at', NULL)->where('status', 0)->where('paid', 1);

        if (auth('api')->user()->role==14 || auth('api')->user()->role==15) {
            $kitchen_id = auth('api')->user()->getOriginal('kitchen');

            $query->where('main_kitchen', $kitchen_id);
        }

        if ($request->selected==0) {
            $params['services'] =  $query->get();
        }
        else{
            $params['services'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();

        return $params;
    }

    public function markservice($id)
    {
        $food = ServiceItem::where('deleted_at', NULL)->find($id);
        if ($food) {
            $kitchen_id = $food->getOriginal('kitchen');
            $kitchen = FoodKitchen::find($kitchen_id);
            if ($kitchen) {
                $kitchen->number = $kitchen->number - $food->qty;
                $kitchen->update();        
            }

            $food->status = 1;
            $food->update();
        }

        return 'ok';
    }

    public function show(Request $request, $code, $id)
    {
        $params = [];
        $query = FoodKitchen::where('food_kitchen.deleted_at', NULL)->where('food_kitchen.kitchen_id', $id)
            ->join('foods', 'food_kitchen.food_id', '=', 'foods.id')
            ->where('foods.deleted_at', NULL);


            if ($request->name) {
                $query->where('foods.name', 'like', '%' . $request->name . '%')->orWhere('foods.code', 'like', '%' . $request->name . '%');
            }

            //return $request;
            $query->select(
                'food_kitchen.id as id',
                'foods.name as item',
                'food_kitchen.number as number',
                'foods.amount as amount',
                'foods.period as period'
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
