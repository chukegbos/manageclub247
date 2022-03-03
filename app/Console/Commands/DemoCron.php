<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Auth;
use App\Inventory;
use App\InventoryStore;
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
use App\Item;
use App\ServiceItem;
use App\Purchase;
use App\ItemPurchase;
use App\Fill;
use App\Product;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use App\Suspend;
use App\Setting;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Synchronization';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
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
        foreach ($members as $member) {
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
        }

        $Users = User::where('deleted_at', NULL)->where('role', 0)->get();
        foreach ($Users as $mem) {
            $user = User::find($mem->id);
            $member = Member::where('deleted_at', NULL)->where('membership_id', $user->unique_id)->first();

            $suspend = Suspend::where('deleted_at', NULL)->where('status', 0)->where('membership_id', $user->unique_id)->first();
            $death = Death::where('deleted_at', NULL)->where('member_id', $user->unique_id)->first();
            $debit = PaymentDebit::where('deleted_at', NULL)->where('member_id', $member->member_id)->where('period', 0)->where('door_access', 1)->first();
            $approved = User::where('deleted_at', NULL)->where('approved', 1)->where('unique_id', $user->unique_id)->first();

            if (!$suspend && !$death && !$debit && $approved && $member->phone_1) {
                $user->door_access = 1;
            }
            else{
                $user->door_access = 0;
            }
            $user->update();
        }

        Fill::create([
            'user_id' => 1,
        ]);
        \Log::info("system synchronization done!");
        $this->info('Demo:Cron Cummand Run successfully!');

    }
}
