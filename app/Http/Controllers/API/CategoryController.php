<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Inventory;
use App\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
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
        

        $query =  Category::where('deleted_at', NULL)->latest();

        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['categories'] =  $query->get();
        }
        else{
            $params['categories'] =  $query->paginate($request->selected);
        }

        $query1 = Category::where('deleted_at', NULL);
        if ($request->name) {
            $query1->where('name', 'like', '%' . $request->name . '%');
        }
        $params['all'] = $query1->count();
       
        return$params;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        return Category::create([
            'name' => ucwords($request->name),
        ]);


    }

    public function show($id)
    {
        $category =  Category::where('deleted_at', NULL)->find($id);
        if ($category) {
            return response()->json($category);
        }
        return ['error' => 'Not found'];
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $category = Category::where('deleted_at', NULL)->find($id);
        $category->name = $request->name;
       
        if($category->save()){
            return ['success'=> 'Data updated successfully'];
        }else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function destroy(Request $request)
    {
        foreach ($request->selected as $id) {
            if ($id!=1) {
                $category = Category::where('deleted_at', NULL)->find($id);
                $inventories = Inventory::where('deleted_at', NULL)->where('category', $id)->get();
                foreach ($inventories as $inventory) {
                    $product = Inventory::find($inventory->id);
                    $product->category = 1;
                    $product->update();
                }
                Category::destroy($id);
            }
        }
        return 'ok';
        
    }
}
