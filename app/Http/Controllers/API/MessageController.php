<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Message;
use App\MessageLog;
use Carbon\Carbon;
use App\User;
use App\Member;
use App\PaymentDebit;
use Illuminate\Support\Facades\Hash;
use DB;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];
        $query = Message::where('deleted_at', NULL);


        if ($request->name) {
            $query->where('messages.name', 'like', '%' . $request->name . '%')->orWhere('messages.code', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['messages'] =  $query->get();
        }
        else{
            $params['messages'] =  $query->paginate($request->selected);
        }

        $params['all'] = $query->count();

        return $params;
    }

    private function prep_number($phone)
    {
        if(substr($phone, 0, 3) == '234') return $phone;

        $first_char = substr(trim($phone), 0, 1);

        if($first_char == '0' || $first_char == '+')
        {
            return '234' . substr($phone, 1);
        }
        else
        {
            return $phone;
        }
    }

    public function store(Request $request)
    {
        $members = User::where('users.deleted_at', NULL)
            ->join('default_esc_members', 'users.unique_id', 'default_esc_members.membership_id')
            ->where('default_esc_members.member_type', '!=', 14)
            ->get();

        if ($request->message_type==0) {
            $message = $request->message;
            if ($request->people == 0) {          
            }
            else {
                $msg = Message::create([
                    'user_id' => auth('api')->user()->id,
                    'sender_name' => 'ESPORTSCLUB',
                    'message' => 'Custom Message',
                    'page_count' => $request->pages,
                ]);

                foreach ($members as $member) {
                    if($member->phone_1 || $member->phone_2 || $member->phone){
                        if ($member->phone) {
                            $phone = $member->phone;
                        }
                        elseif ($member->phone_1) {
                            $phone = $member->phone_1;
                        }
                        else {
                            $phone = $member->phone_2;
                        }

                        $data1 = [
                            'from' => 'ESPORTSCLUB',
                            'to' => $this->prep_number($phone),
                            'text' => $message,
                        ];

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://api.silversands.com.ng/sms/1/text/single",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30000,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => json_encode($data1),
                            CURLOPT_HTTPHEADER => array(
                                // Set here requred headers
                                "accept: */*",
                                "accept-language: en-US,en;q=0.8",
                                "content-type: application/json",
                                "Authorization: Basic ZW51Z3VzcG9ydHM6Q3VARW51MjAyMQ==",
                            ),
                        ));

                        $response = curl_exec($curl);
                        $err = curl_error($curl);
                        curl_close($curl);

                        MessageLog::create([
                            'message_id' => $msg->id,
                            'member_id' => $member->unique_id,
                            'phone' => $phone,
                        ]);
                    }
                }

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
            }
        }
        
        elseif ($request->message_type==1) {
            $msg = Message::create([
                'user_id' => auth('api')->user()->id,
                'sender_name' => 'ESPORTSCLUB',
                'message' => 'Debit Message',
                'page_count' => $request->pages,
            ]);

            foreach ($members as $member) {
                if($member->phone_1 || $member->phone_2 || $member->phone){
                    $the_member = Member::where('deleted_at', NULL)->where('membership_id', $member->unique_id)->first();

                    if ($member->phone) {
                        $phone = $member->phone;
                    }
                    elseif ($member->phone_1) {
                        $phone = $member->phone_1;
                    }
                    else {
                        $phone = $member->phone_2;
                    }

                    $debt  = PaymentDebit::where('member_id', $the_member->id)->where('status', 0)->where('date_entered', '<=', $request->end_date)->sum('amount');
                    
                    $myDate = Carbon::createFromTimeStamp(strtotime($request->end_date))->toFormattedDateString();

                    $message = 'Memb ID: '.$member->unique_id. '; Debt Bal: N'.$debt.'; M.Wallet: N'.$member->wallet_balance.'; L.Wallet: N' . $member->credit_unit. '; K/B Wallet: N' . $member->bar_wallet. ' as at '. $request->end_date.' Enugu Sports Club (FCMB ACCT NO) 1685653012';

                    $data1 = [
                        'from' => 'ESPORTSCLUB',
                        'to' => $this->prep_number($phone),
                        'text' => $message,
                    ];

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.silversands.com.ng/sms/1/text/single",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30000,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => json_encode($data1),
                        CURLOPT_HTTPHEADER => array(
                            // Set here requred headers
                            "accept: */*",
                            "accept-language: en-US,en;q=0.8",
                            "content-type: application/json",
                            "Authorization: Basic ZW51Z3VzcG9ydHM6Q3VARW51MjAyMQ==",
                        ),
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);

                    MessageLog::create([
                        'message_id' => $msg->id,
                        'member_id' => $member->unique_id,
                        'phone' => $phone,
                    ]);
                }
            }
        }
        return 'ok';
    }
}
