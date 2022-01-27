<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Inventory;
use App\Notification;
use App\User;
use App\Sale;
use App\Debit;
use App\Store;
use App\Member;
use App\Room;
use App\Death;
use App\PaymentDebit;
use App\Payment;
use App\PaymentProduct;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use App\Ledger;
use App\AccountType;
use App\Account;
use App\Suspend;
use Mail;
use Illuminate\Support\Facades\URL;
use App\Setting;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function ledgerID()
    {
        $last_ledger = Ledger::where('deleted_at', null)->latest()->first();
        if (!$last_ledger) {
            $ledger_id = 100000;
        }
        else{
           $ledger_id = $last_ledger->ledger_id + 100; 
        }

        return $ledger_id;
    }

    public function index()
    {
        set_time_limit(0);

        /*$all_debits = array();

        $members = Member::where('deleted_at', NULL)->get();
        foreach ($members as $key => $member) {
            $get_debit = array();
            $each_debit = PaymentDebit::where('member_id', $member->id)->get();

            foreach ($each_debit as $dt) {
                $amt = $dt->amount * $dt->period;
                array_push($get_debit, $amt);
            }

            $sum_debit =  array_sum($get_debit);
            $sum_credit = Payment::where('member_id', $member->id)->sum('amount');

            $debit =
            [
                'name' => $member->first_name. ' '. $member->last_name,
                'description' => 'Outstanding balance as at migration', 
                'debit' => $sum_debit,
                'credit' => $sum_credit,
                'balance' => $sum_debit - $sum_credit,
                'member_id' => $member->id,
                'membership_id' => $member->membership_id,                
            ];

            array_push($all_debits, $debit);
        }
    
        foreach ($all_debits as $dbt) {
            $payment_debit = Debit::create([
                'member_id' => $dbt['member_id'],
                'product' => 309,
                'amount' => $dbt['balance'],
                'description' => $dbt['description'],
                'debit_type' => 1,
                'grace_period' => 16,
                'date_entered' => Carbon::today(),
                'start_date' => Carbon::today(),
                'created_by' => auth()->user()->id,
            ]);
        }

        $all_debits = Debit::get();
        foreach ($all_debits as $dbt) {
            PaymentDebit::create([
                'member_id' => $dbt->member_id,
                'product' => $dbt->product,
                'amount' => $dbt->amount,
                'description' => $dbt->description,
                'debit_type' => 1,
                'grace_period' => 16,
                'date_entered' => Carbon::today(),
                'start_date' => Carbon::today(),
                'created_by' => auth()->user()->id,
            ]);
        }

        $members = Member::where('deleted_at', NULL)->get();
        foreach ($members as $user) {
            $the_user = User::where('deleted_at', NULL)->where('unique_id', $user->membership_id)->first();

            if (!$the_user) {
                if ($user->email) {
                    $email = $user->email;
                }
                else{
                    $email = $user->membership_id.'@enugusportsclub.org';
                }

                $mail_user = User::where('deleted_at', NULL)->where('unique_id', NULL)->where('email', $email)->first();
                if ($mail_user) {
                    $mail_user->unique_id = $user->membership_id;
                    $mail_user->update();
                }
                else {
                    User::create([
                        'unique_id' => $user->membership_id,
                        'name' => $user->last_name.' '.$user->first_name.' '.$user->middle_name,
                        'c_person' => $user->last_name.' '.$user->first_name.' '.$user->middle_name,
                        'email' => $email,
                        'credit_unit' => 0,
                        'wallet_balance' => 0,
                        'bar_wallet' => 0,
                        'door_access' => 1,
                        'role' => 0,
                        'password' => Hash::make('Father@1989'),
                    ]);
                }
            }
        }*/
        return view('dashboard');
    }

    public function getUser()
    {
        return Auth::user();
    }


    public function sync()
    {
        set_time_limit(0);
        $inventories = Inventory::where('deleted_at', NULL)->get();
        $stores = Store::where('deleted_at', NULL)->get();

        foreach ($inventories as $inventory) {
            foreach ($stores as $store) {
                $product = DB::table('inventory_store')
                    ->where('deleted_at', NULL)
                    ->where('store_id', $store->id)
                    ->where('inventory_id', $inventory->id)
                    ->first();
                if (!$product) {
                    $store->inventory()->attach($inventory->id);
                }

                $room = Room::where('deleted_at', NULL)
                    ->where('store_id', $store->id)
                    ->where('inventory_id', $inventory->id)
                    ->first();
                if (!$room) {
                    $get_room = new Room();
                    $get_room->inventory_id = $inventory->id;
                    $get_room->store_id = $store->id;
                    $get_room->number = 0;
                    $get_room->save();
                }
            }
        }

        $pays = Payment::get();
        foreach ($pays as $pay) {
            $the_pay = Payment::find($pay->id);
            $the_pay->rec_id = 'ESC'.$the_pay->debit_id.$the_pay->member_id;
            $the_pay->update();
        }
        //sync payment with debit
        /*$allpayment = Payment::get();
        foreach ($allpayment as $pay) {
            $getdebt = PaymentDebit::where('deleted_at', NULL)->find($pay->debit_id);
            if (($getdebt) && (($getdebt->status==0) || ($getdebt->status==NULL))) {
                $getdebt->status = 1;
                $getdebt->update();
            }
        }*/


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

        /*$pproducts = PaymentProduct::where('type', 1)->where('deleted_at', NULL)->get();
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

        //Phone Switch
        $phoneusers = User::where('deleted_at', NULL)->where('role', 0)->where('phone', NULL)->get();
        foreach ($phoneusers as $user) {
            $user = User::find($user->id);
            $user->door_access = 0;
            $user->update();
        }

        $users = User::where('deleted_at', NULL)->where('role', 0)->get();
        foreach ($users as $user) {
            $member = Member::where('membership_id', $user->unique_id)->first();
            $user = User::find($user->id);

            if ($member->entrance_date && !$user->entrance_date) {
                $user->entrance_date = date('Y/m/d', $member->entrance_date);
            }

            $member = Member::where('membership_id', $user->unique_id)->where('member_type', '!=', 14)->first();
            foreach ($pproducts as $pp) {
                $findPayment = PaymentDebit::where('member_id', $member->id)->where('product', $pp->id)->where('year', $dt->year)->where('month', $dt->month)->first();

                if ((!$findPayment )&& ($dt->day >= $pp->reoccuring_day)) {
                    $payment_debit = PaymentDebit::create([
                        'member_id' => $member->id,
                        'product' => $pp->id,
                        'amount' => $pp->amount,
                        'description' => ucwords($month.' '.$year. ' Monthly Payment for '.$pp->payment_name),
                        'debit_type' => 0,
                        'grace_period' => $pp->grace_period,
                        'month' => $dt->month,
                        'year' => $dt->year,
                        'date_entered' => Carbon::today(),
                        'start_date' => $dt->year.'-'.$dt->month.'-'.$pp->reoccuring_day,
                        'created_by' => auth()->user()->id,
                    ]);
                }
            }
            $user->update();
        }*/

        //Debt Switch
        /*$members = Member::where('deleted_at', NULL)->get();

        foreach ($members as $user) {
            $debit = PaymentDebit::where('deleted_at', NULL)->where('member_id', $user->id)->where('period', 0)->first();
            if ($debit) {
                $user = User::find($user->id);
                $user->door_access = 0;
                $user->update();
            }

            $approved = User::where('deleted_at', NULL)->where('approved', 0)->where('unique_id', $user->membership_id)->first();
            if ($approved) {
                $approveduser = User::find($approved->id);
                $approveduser->door_access = 0;
                $approveduser->update();
            }
        }

        $sameUsers = User::where('deleted_at', NULL)->where('role', 0)->get();
        foreach ($sameUsers as $user) {
            $user = User::find($user->id);
            $user->approved = 1;
            $user->update();

            $suspend = Suspend::where('deleted_at', NULL)->where('status', 0)->where('membership_id', $user->unique_id)->first();
            $death = Death::where('deleted_at', NULL)->where('member_id', $user->unique_id)->first();
            $debit = PaymentDebit::where('deleted_at', NULL)->where('member_id', $user->unique_id)->where('period', 0)->first();
            $approved = User::where('deleted_at', NULL)->where('approved', 0)->where('unique_id', $user->unique_id)->first();
            if (!$suspend && !$death && !$debit && !$approved && $user->phone) {
                $user = User::find($user->id);
                $user->door_access = 1;
                $user->update();
            }
        }
 
        $alldebits = PaymentDebit::where('deleted_at', NULL)->where('status', 0)->get();
        foreach ($alldebits as $debt) {
            if (!$debt->date_entered && $debt->date_added) {
                $db = PaymentDebit::find($debt->id);
                $db->date_entered = date('Y/m/d', $debt->date_added);
                $db->update();
            }
        }*/
        return redirect()->route('index');
    }
}
