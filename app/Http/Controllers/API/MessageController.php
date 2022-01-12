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
            ->where('users.phone', '!=', NULL)
            ->join('default_esc_members', 'users.unique_id', 'default_esc_members.membership_id')
            ->where('default_esc_members.member_type', '!=', 14)
            ->get();

        if ($request->message_type==0) {
            $message = $request->message;
            if ($request->people == 0) {
                // Make Post Fields Array
                $data1 = [
                    'from' => 'ESPORTSCLUB',
                    'to' => '2348066267671',
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

                if ($response) {
                    return $response;
                }
                else {
                    return $err;
                }
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

                $data3 = [
                    'from' => 'ESPORTSCLUB',
                    'to' => '2348066267671',
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
                    CURLOPT_POSTFIELDS => json_encode($data3),
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

                $data2 = [
                    'from' => 'ESPORTSCLUB',
                    'to' => '2348176573133',
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
                    CURLOPT_POSTFIELDS => json_encode($data2),
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
                    if ($member->phone) {
                        $phone = $member->phone;
                    }
                    elseif ($member->phone_1) {
                        $phone = $member->phone_1;
                    }
                    else {
                        $phone = $member->phone_2;
                    }

                    $the_member = Member::where('deleted_at', NULL)->where('membership_id', $member->unique_id)->first();

                    $debt  = PaymentDebit::where('member_id', $the_member->id)->where('status', 0)->where('date_entered', '<=', $request->end_date)->sum('amount');
                    
                    $myDate = Carbon::createFromTimeStamp(strtotime($request->end_date))->toFormattedDateString()

                    $message = 'Enugu Sports Club Membership ID: '.$member->unique_id. ';  
                    Debt Bal: N'.$debt.'; Wallet Bal: N'.$member->wallet_balance. ' as at '. $request->end_date.' For clarifications contact Front Desk.';

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

            $message1 = 'If you recieve this message sir, just know i have sent the debit sms and it delivered';
            
            $data3 = [
                'from' => 'ESPORTSCLUB',
                'to' => '2348066267671',
                'text' => $message1,
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
                CURLOPT_POSTFIELDS => json_encode($data3),
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

            $data2 = [
                'from' => 'ESPORTSCLUB',
                'to' => '2348176573133',
                'text' => $message1,
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
                CURLOPT_POSTFIELDS => json_encode($data2),
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
            return 'ok';
        }

        
    }
}
