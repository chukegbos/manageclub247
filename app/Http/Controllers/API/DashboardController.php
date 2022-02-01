<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Purchase;
use App\Inventory;
use App\Item;
use App\Fund;
use App\Member;
use App\Category;
use App\Login;
use DB;
use App\RoomMovement;
use App\Store;
use App\Sale;
use App\Debtor;
use App\DebtorHistory;
use App\ServiceItem;
use App\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    private function numberTowords($num){
        $ones = array(
            0 =>"Zero",
            1 => "One",
            2 => "Two",
            3 => "Three",
            4 => "Four",
            5 => "Five",
            6 => "Six",
            7 => "Seven",
            8 => "Eight",
            9 => "Nine",
            10 => "Ten",
            11 => "Eleven",
            12 => "Twelve",
            13 => "Thirteen",
            14 => "Fourteen",
            15 => "Fifteen",
            16 => "Sixteen",
            17 => "Seventeen",
            18 => "Eighteen",
            19 => "Nineteen",
            "014" => "Fourteen"
        );

        $tens = array( 
            0 => "Zero",
            1 => "Ten",
            2 => "Twenty",
            3 => "Thirty", 
            4 => "Forty", 
            5 => "Fifty", 
            6 => "Sixty", 
            7 => "Seventy", 
            8 => "Eighty", 
            9 => "Ninety" 
        ); 

        $hundreds = array( 
            "Hundred", 
            "Thousand", 
            "Million", 
            "Billion", 
            "Trillion", 
            "Quardrillion" 
        ); 

        /*limit t quadrillion */
        $num = number_format($num,2,".",","); 
        $num_arr = explode(".",$num); 
        $wholenum = $num_arr[0]; 
        $decnum = $num_arr[1]; 
        $whole_arr = array_reverse(explode(",",$wholenum)); 
        krsort($whole_arr,1); 
        $rettxt = ""; 

        foreach($whole_arr as $key => $i){
            
            while(substr($i,0,1)=="0")
            $i=substr($i,1,5);

            if($i < 20){ 
                /* echo "getting:".$i; */
                $rettxt .= $ones[$i]; 
            }
            elseif($i < 100){ 
                if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
                if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)]; 
            }
            else{ 
                if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
                if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)]; 
                if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)]; 
            } 

            if($key > 0){ 
             $rettxt .= " ".$hundreds[$key]." "; 
            }
        } 

        if($decnum > 0){
            $rettxt .= " and ";
            if($decnum < 20){
                $rettxt .= $ones[$decnum];
            }
            elseif($decnum < 100){
                $rettxt .= $tens[substr($decnum,0,1)];
                $rettxt .= " ".$ones[substr($decnum,1,1)];
            }
        }

        return $rettxt;
    }

    public function getorder($id)
    {
        $params = [];
        $params['sale'] = Sale::where('sales.deleted_at', NULL)
            ->where('sales.sale_id', $id)
            ->join('users', 'sales.market_id', '=', 'users.id')
            ->select(
                'sales.id as id',
                'sales.approved as approved',
                'sales.status as status',
                'sales.sale_id as sale_id',
                'sales.store_id as store_id',
                'sales.buyer as buyer',
                'sales.mop as mop',
                'users.name as marketer',
                'sales.user_type as user_type',
                'sales.guest as guest',
                'sales.user_id as user_id',
                'sales.totalPrice as totalPrice',
                'sales.main_date as created_at'  
            )
            ->first();

        if ($params['sale'] ) {
            //$params['word_price'] = $this->numberTowords($params['sale']->totalPrice);
            if ($params['sale']->buyer) {
                $params['customer'] = Member::where('deleted_at', NULL)->find($params['sale']->buyer);
            }
            else {
                $params['customer'] = $params['sale']->guest;

            }
            $params['store'] = Store::where('deleted_at', NULL)->find($params['sale']->getOriginal('store_id'));
            
            $params['items'] = Item::where('items.code', $id)
                
                ->join('inventories', 'items.product_id', '=', 'inventories.id')
                ->where('inventories.deleted_at', NULL)
                ->select(
                    'items.id as id',
                    'items.product_id as product_id',
                    'items.qty as qty',
                    'items.discount as discount',
                    'inventories.quantity as quantity',
                    'inventories.product_name as product_name',
                    'inventories.price as price' 
                )
            ->get();
            $params['services'] = ServiceItem::where('code', $id)->where('deleted_at', NULL)->get();

            if ($params['sale']->getOriginal('mop')==0) {
                $debtor = Debtor::where('deleted_at', NULL)->where('trans_id', $params['sale']->sale_id)->first();
                if ($debtor) {
                    $params['histories'] = DebtorHistory::where('deleted_at', NULL)->where('debtor_id', $debtor->id)->get();
                    $total_paid = array();
                    foreach ($params['histories'] as $hist) {
                        array_push($total_paid, $hist->amount_paid);
                    }
                    $params['total_paid'] = array_sum($total_paid);
                }
            }

            return $params;
        }
        else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function stat(Request $request)
    {
        $params = [];
        $user = auth('api')->user();
        $params['users'] = User::where('deleted_at', NULL)->where('role', '!=', 0)->count();
        $params['customers'] = User::where('deleted_at', NULL)->where('role', 0)->count();
        $params['inventories'] = Inventory::where('deleted_at', NULL)->count();
        $params['categories'] = Category::where('deleted_at', NULL)->count();
        $params['transactions'] = Sale::where('deleted_at', NULL)->where('status', 'concluded')->count();

        $params['fund'] = Fund::where('funds.deleted_at', NULL)->where('status', 'Pending')->count();
        $params['approve_purchase'] = Purchase::where('deleted_at', NULL)->where('status', 0)->where('store_id', $user->store)->count();
        $params['accept_purchase'] = Purchase::where('deleted_at', NULL)->where('status', 1)->where('store_id', $user->store)->where('status_accept', 0)->where('store_id', $user->store)->count();

        $params['moved'] = RoomMovement::where('deleted_at', NULL)
            ->where('moved', $user->store)
            ->where('status', 'requested')->count();
        
        $params['requested1'] = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.store_id', $user->store)
            ->where('rooom_movement.status', 'approved')
            ->join('inventories', 'rooom_movement.product_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->count();

        $params['requested2'] = RoomMovement::where('rooom_movement.deleted_at', NULL)
            ->where('rooom_movement.store_id', $user->store)
            ->Where('rooom_movement.status', 'requested')
            ->join('inventories', 'rooom_movement.product_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)->count();

        $params['totalPrice'] = Sale::where('deleted_at', NULL)->where('status', 'concluded')->sum('totalPrice');
        return $params;
    }


    public function logins(Request $request)
    {
        $params = [];
        $user = auth('api')->user();
        
        $query = Login::where('logins.deleted_at', NULL)
            ->join('users', 'logins.user_id', '=', 'users.id')
            ->where('users.deleted_at', NULL)
            ->join('stores', 'logins.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->select(
                'logins.id as id',
                'logins.logout as logout',
                'logins.status as status',
                'logins.verified_by as verified_by',
                'logins.created_at as login',
                'stores.name as bar_name',
                'users.name as manager'
            );

        if ($request->selected==0) {
            $params['logins'] =  $query->get();
        }
        else{
            $params['logins'] =  $query->paginate($request->selected);
        }
        return $params;
    }

    public function approvelogin($id)
    {
        $user = auth('api')->user();
        
        $login = Login::where('deleted_at', NULL)->find($id);
        $login->status = 1;
        $login->verified_by = $user->id;
        $login->update();
        return 'ok';
    }
}
