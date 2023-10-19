<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Purchase;
use App\Inventory;
use App\PaymentDebit;
use App\Suspend;
use App\Death;
use App\Fill;
use App\Product;
use App\Item;
use App\Fund;
use App\Member;
use App\Category;
use App\Kitchen;
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
        $params['sale'] = Sale::where('deleted_at', NULL)->where('sale_id', $id)->first();

        if ($params['sale'] ) {
            //$params['word_price'] = $this->numberTowords($params['sale']->totalPrice);
            if ($params['sale']->buyer) {
                $params['customer'] = Member::where('deleted_at', NULL)->find($params['sale']->getOriginal('buyer'));
            }
            else {
                $params['customer'] = $params['sale']->guest;

            }
            $params['store'] = Store::where('deleted_at', NULL)->find($params['sale']->getOriginal('store_id'));
            $params['kitchen'] = Kitchen::where('deleted_at', NULL)->find($params['sale']->getOriginal('sec_id'));
            
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
        $this->sync();
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
            ->select(
                'logins.id as id',
                'logins.logout as logout',
                'logins.status as status',
                'logins.verified_by as verified_by',
                'logins.created_at as login',
                'logins.kitchen as kitchen',
                'logins.store as store',
                'logins.kitchen_id as kitchen_id',
                'logins.store_id as store_id',
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

    public function sync()
    {
       
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        /*$sales = Sale::where('deleted_at', NULL)->where('status', 'concluded')->get();
        foreach ($sales as $sale) {
            $items = Item::where('deleted_at', NULL)->where('code', $sale->sale_id)->get();
            foreach ($items as $item) {
                $product = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $item->product_id)->where('store_id', $sale->getOriginal('store_id'))->first();

                if ($product) {
                    $product->number = $product->number - $item->qty;
                    $product->update();
                }
            }
        }
        $inventories = Inventory::where('deleted_at', NULL)->get();
        $stores = Store::where('deleted_at', NULL)->get();

        foreach ($inventories as $inventory) {
            foreach ($stores as $store) {
                $product = InventoryStore::where('deleted_at', NULL)
                    ->where('store_id', $store->id)
                    ->where('inventory_id', $inventory->id)
                    ->first();
                if (!$product) {
                    InventoryStore::create([
                        'inventory_id' =>$inventory->id,
                        'store_id' => $store->id,
                        'number' => 0,
                    ]);
                }

            }
        }

        $purchases = Purchase::where('deleted_at', NULL)->get();
        foreach ($purchases as $purchase) {
            $items = ItemPurchase::where('deleted_at', NULL)->where('purchase_id', $purchase->purchase_id)->get();
            foreach ($items as $item) {
                $inventory = InventoryStore::where('deleted_at', NULL)->where('inventory_id', $item->product_id)->where('store_id', 1)->first();
                $inventory->number = $inventory->number + $item->qty;
                $inventory->update();
            }
        }
   
        $pays = Payment::get();
        foreach ($pays as $pay) {
            $the_pay = Payment::find($pay->id);
            $the_pay->rec_id = 'ESC'.$the_pay->debit_id.$the_pay->member_id;
            $the_pay->update();
        }
        //sync payment with debit
        $allpayment = Payment::get();
        foreach ($allpayment as $pay) {
            $getdebt = PaymentDebit::where('deleted_at', NULL)->find($pay->debit_id);
            if (($getdebt) && (($getdebt->status==0) || ($getdebt->status==NULL))) {
                $getdebt->status = 1;
                $getdebt->update();
            }
        }*/

        $debits = PaymentDebit::where('deleted_at', NULL)->get();
        $today = Carbon::today();
        foreach ($debits as $db) {
           
            $product = Product::where('deleted_at', NULL)->find($db->getOriginal('product'));
            if ($product) {
                $grace_period = $product->grace_period;
                $door_access = $product->door_access;
            }
            else {
                $grace_period = 30;
                $door_access = 0;

            }
            
            if (($db->start_date) && ($today > Carbon::parse($db->start_date)->addDays($grace_period))) {
                $period = 0;
            }
            else {
                $period = 1;
            }


            $debt = PaymentDebit::where('deleted_at', NULL)->find($db->id);
            $debt->grace_period = $grace_period;
            $debt->door_access = $door_access;
            $debt->period = $period;
            $debt->update();
        }

        $suspends = Suspend::where('deleted_at', NULL)->where('status', 0)->where('end_date', '<=', Carbon::today())->get();
        foreach ($suspends as $suspend) {
            $getSus = Suspend::find($suspend->id);
            $getSus->status = 1;
            $getSus->unsuspended_by = 0;
            $getSus->reason = 'Unsuspended by system because the duration has elapsed.';
            $getSus->update();

            $member = Member::where('deleted_at', NULL)->where('membership_id', $getSus->membership_id)->first();
            $member->update([
                'member_type' => $getSus->former_type,
            ]);

            $getUser = User::where('deleted_at', NULL)->where('unique_id', $getSus->membership_id)->first();

            $getUser->update([
                'door_access' => 1,
            ]);
        }

        $pproducts = Product::where('type', 1)->where('deleted_at', NULL)->get();
        $dt = Carbon::today();

        switch ($dt->month) {
            case 1:
                $month = 'February';
                $year = $dt->year;
                break;

            case 2:
                $month = 'March';
                $year = $dt->year;
                break;

            case 3:
                $month = 'April';
                $year = $dt->year;
                break;

            case 4:
                $month = 'May';
                $year = $dt->year;
                break;

            case 5:
                $month = 'June';
                $year = $dt->year;
                break;

            case 6:
                $month = 'July';
                $year = $dt->year;
                break;

            case 7:
                $month = 'August';
                $year = $dt->year;
                break;

            case 8:
                $month = 'September';
                $year = $dt->year;
                break;

            case 9:
                $month = 'October';
                $year = $dt->year;
                break;

            case 10:
                $month = 'November';
                $year = $dt->year;
                break;

            case 11:
                $month = 'December';
                $year = $dt->year;
                break;
            
            default:
                $month = 'January';
                $year = $dt->year + 1;
                break;
        }

        //product check
        $members = Member::where('member_type', '!=', 14)->get();
        /*foreach ($members as $member) {
            foreach ($pproducts as $pp) {
                $findPayment = PaymentDebit::where('member_id', $member->id)->where('product', $pp->id)->where('year', $dt->year)->where('month', $dt->month)->first();

                if ((!$findPayment )&& ($dt->day >= $pp->reoccuring_day)) {
                    $payment_debit = PaymentDebit::create([
                        'member_id' => $member->id,
                        'product' => $pp->id,
                        'amount' => $pp->amount,
                        'description' => ucwords($month.' '.$year. ' Monthly Payment for '.$pp->payment_name),
                        'debit_type' => 0,
                        'grace_period' => ($pp->grace_period) ? $pp->grace_period : 30,
                        'month' => $dt->month,
                        'year' => $dt->year,
                        'date_entered' => Carbon::today(),
                        'start_date' => $dt->year.'-'.$dt->month.'-'.$pp->reoccuring_day,
                        'created_by' => auth()->user()->id,
                    ]);
                }
            }
        }*/
        $Users = User::where('deleted_at', NULL)->where('role', 0)->get();
        foreach ($Users as $mem) {
            $user = User::find($mem->id);
            $member = Member::where('deleted_at', NULL)->where('membership_id', $user->unique_id)->first();
            if($member){
                $suspend = Suspend::where('deleted_at', NULL)->where('status', 0)->where('membership_id', $user->unique_id)->first();
                $death = Death::where('deleted_at', NULL)->where('member_id', $user->unique_id)->first();
                if($death){
                    $member->member_type = 14;
                    $member->update();
                }
                $debit = PaymentDebit::where('deleted_at', NULL)->where('member_id', $member->id)->where('period', 0)->where('status', 0)->where('door_access', 1)->first();
                $approved = User::where('deleted_at', NULL)->where('approved', 0)->where('unique_id', $user->unique_id)->first();

                if ($suspend || $death || $approved || $debit || !$member->phone_1) {
                    $user->door_access = 0;
                }
                else{
                $user->door_access = 1; 
                }
            }
            /*if (!$suspend && !$death && !$debit && $approved && $member->phone_1) {
                $user->door_access = 1;
            }
            else{
                $user->door_access = 0;
            }*/
            $user->update();
        }

        Fill::create([
            'user_id' => auth()->user()->id,
        ]);
        return redirect()->route('index');
    }
}
