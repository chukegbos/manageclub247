<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AccountTax;
use Illuminate\Support\Str;
use DB;

class AccountTaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }


    public function index(Request $request)
    {
        $query =  AccountTax::where('deleted_at', NULL);
        if($request->name)
        {
            $query->where('name', $request->name);
        }

        $type = $query->paginate(10);
        return $type;
    }

    public function search(Request $request)
    {
        $search_term = $request[0];
        $type = AccountTax::where('deleted_at', NULL)->where('name', 'like', '%' . $search_term . '%')->get();
        return $type;
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        
        return AccountTax::create([
            'name' => $request->name,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $type = AccountTax::where('deleted_at', NULL)->find($id);
        $type->name = $request->name;
        $type->update();
       
        if($type->save()){
            return ['success'=> 'Data updated successfully'];
        }else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function destroy($id)
    {
        $account = AccountTax::where('deleted_at', NULL)->find($id);
        $account->delete();
    }
}
