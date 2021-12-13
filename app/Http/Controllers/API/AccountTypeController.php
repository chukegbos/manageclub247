<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AccountType;
use Illuminate\Support\Str;
use DB;

class AccountTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }


    public function index(Request $request)
    {
        $query =  AccountType::where('deleted_at', NULL)->orderBY('id', 'asc');
        if($request->name)
        {
            $query->where('name', $request->name);
        }

        if($request->type)
        {
            $query->where('type', $request->type);
        }

        $type = $query->paginate(10);
        return $type;
    }

    public function search(Request $request)
    {
        $search_term = $request[0];
        $type = AccountType::where('deleted_at', NULL)->where('name', 'like', '%' . $search_term . '%')->get();
        return $type;
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        
        return AccountType::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $type = AccountType::where('deleted_at', NULL)->find($id);
        $type->name = $request->name;
        $type->type = $request->type;
        $type->update();
       
        if($type->save()){
            return ['success'=> 'Data updated successfully'];
        }else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function destroy($id)
    {
        $account = AccountType::where('deleted_at', NULL)->find($id);
        $account->delete();
    }
}
