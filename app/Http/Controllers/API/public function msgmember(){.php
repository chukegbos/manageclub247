    public function msgmember(){
        return Message::create([
            'user_id' => auth('api')->user()->id,
            'sender_name' => 'ESPORTSCLUB',
            'message' => 'Group Debit Message',
            'page_count' => 1,
        ]);
         
    }

    public function makeDebit($member_id, $product_id, $amount, $description, $debit_type, $grace_period){
        $payment_debit = PaymentDebit::create([
            'member_id' => $member_id,
            'product' => $product_id,
            'amount' => $amount,
            'description' => ucwords($description),
            'debit_type' => $debit_type,
            'grace_period' => $grace_period,
            'start_date' => Carbon::today(),
            'date_entered' => Carbon::today(),
            'created_by' => auth('api')->user()->id,
        ]);

        return $payment_debit;
    }
    
    public function makeCredit($member_id, $debit_id, $amount){
        Payment::create([
            'debit_id' => $debit->id,
            'member_id' => $member_id,
            'amount' => $amount,
            'payment_channel' => 6,
            'created_by' => auth('api')->user()->id,
        ]);
    }

    public function sendMessage($data, $msg_id, $unique_id, $phone){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.silversands.com.ng/sms/1/text/single",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
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

        if($response){
            MessageLog::create([
                'message_id' => $msg_id,
                'member_id' => $unique_id,
                'phone' => $phone,
            ]);
            return true;
        }
        return false;
    }

    public function debitmembers(Request $request)
    {
    $this->validate($request, [
        'description' => 'required|string|max:1000',
        'product' => 'required',
        'amount' => 'required',
    ]);

    set_time_limit(0);

    $product = Product::find($request['product']);
   
    if (!$product->wallet) {
        return ['error' => 'This payment product does not have any wallet attached to it, set a wallet to it and try again'];
    }
    $msg = $this->msgmember();

    foreach ($request->people_id as $id){
        $members = Member::where('deleted_at', NULL)->where('member_type', $id)->get();
        foreach ($members as $member) {
            $payment_debit = $this->makeDebit($member->id, $request->product, $request->amount, $request->description, $request->debit_type, $request->grace_period);
            $the_member = User::where('unique_id', $member->membership_id)->first();

            if($the_member){
                if ($product->wallet==0) {
                    $wallet = $the_member->bar_wallet;
                    $the_member->bar_wallet = $the_member->bar_wallet - $request->amount;
                }
                elseif ($product->wallet==1) {
                    $wallet = $the_member->wallet_balance;
                    $the_member->wallet_balance = $the_member->wallet_balance - $request->amount;
                }
                else {
                    $wallet = $the_member->credit_unit;
                    $the_member->credit_unit = $the_member->credit_unit - $request->amount;
                }

                if ($wallet >= $request->amount) {
                    $this->makeCredit($payment_debit->member_id, $payment_debit->id, $request->amount);
                    $payment_debit->status = 1;
                    $payment_debit->update();
                    $the_member->update();
                }
                else{
                    if($member->phone_1 || $member->phone_2 || $the_member->phone){
                        if ($the_member->phone) {
                            $phone = $the_member->phone;
                        }
                        elseif ($member->phone_1) {
                            $phone = $member->phone_1;
                        }
                        else {
                            $phone = $member->phone_2;
                        }

                        $message = 'Membership ID: '.$the_member->unique_id.'; Debit for '.$request->description.'; Amount: N'.$request->amount.' Enugu Sports Club (FCMB ACCT NO) 1685653012';

                        $data = [
                            'from' => 'ESPORTSCLUB',
                            'to' => $this->prep_number($phone),
                            'text' => $message,
                        ];

                        <!-- $this->sendMessage($data, $msg->id, $the_member->unique_id, $phone) -->
                    }
                }
            }
        }

        $suspends = Suspend::where('deleted_at', NULL)->where('former_type', $id)->where('status', 0)->get();
        foreach ($suspends as $suspend) {
            $payment_debit = $this->makeDebit($suspend->membership_id, $request->product, $request->amount, $request->description, $request->debit_type, $request->grace_period);
            $the_member = User::where('unique_id', $suspend->membership_id)->first();
        }
    }

    
    $suspends = Suspend::where('deleted_at', NULL)->where('status', 0)->get();
    foreach ($suspends as $suspend) {
        foreach ($request->people_id as $id) {
            if ($suspend->former_type == $id) {
              

                $member = Member::where('deleted_at', NULL)->where('membership_id', $suspend->membership_id)->first();

                $the_member = User::where('unique_id', $member->membership_id)->first();

                if ($product->wallet==0) {
                    $wallet = $the_member->bar_wallet;
                    $the_member->bar_wallet = $the_member->bar_wallet - $request->amount;
                }
                elseif ($product->wallet==1) {
                    $wallet = $the_member->wallet_balance;
                    $the_member->wallet_balance = $the_member->wallet_balance - $request->amount;
                }
                else {
                    $wallet = $the_member->credit_unit;
                    $the_member->credit_unit = $the_member->credit_unit - $request->amount;
                }

                if (($the_member) && ($wallet >= $request->amount)) {
                    
                    $receipt_number = ($request->receipt_number) ? $request->receipt_number : NULL;
                    $bank = ($request->bank) ? $request->bank : NULL;
                    $pos = ($request->pos) ? $request->pos : NULL;

                    Payment::create([
                        'debit_id' => $payment_debit->id,
                        'member_id' => $payment_debit->member_id,
                        'amount' => $request->amount,
                        'payment_channel' => 6,
                        'created_by' => auth('api')->user()->id,
                        'receipt_number' => $receipt_number,
                        'pos' => $pos,
                        'bank' => $bank,
                    ]);

                    $payment_debit->status = 1;
                    $payment_debit->update();

                    $the_member->update();
                }
                else {
                    if($member->phone_1 || $member->phone_2 || $the_member->phone){
                        if ($the_member->phone) {
                            $phone = $the_member->phone;
                        }
                        elseif ($member->phone_1) {
                            $phone = $member->phone_1;
                        }
                        else {
                            $phone = $member->phone_2;
                        }

                        $message = 'Membership ID: '.$the_member->unique_id.'; Debit for '.$request->description.'; Amount: N'.$request->amount.' Enugu Sports Club (FCMB ACCT NO) 1685653012';

                        $data1 = [
                            'from' => 'ESPORTSCLUB',
                            'to' => $this->prep_number($phone),
                            'text' => $message,
                        ];

                        // $curl = curl_init();

                        // curl_setopt_array($curl, array(
                        //     CURLOPT_URL => "https://api.silversands.com.ng/sms/1/text/single",
                        //     CURLOPT_RETURNTRANSFER => true,
                        //     CURLOPT_ENCODING => "",
                        //     CURLOPT_MAXREDIRS => 10,
                        //     CURLOPT_TIMEOUT => 30000,
                        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        //     CURLOPT_CUSTOMREQUEST => "POST",
                        //     CURLOPT_POSTFIELDS => json_encode($data1),
                        //     CURLOPT_HTTPHEADER => array(
                        //         // Set here requred headers
                        //         "accept: */*",
                        //         "accept-language: en-US,en;q=0.8",
                        //         "content-type: application/json",
                        //         "Authorization: Basic ZW51Z3VzcG9ydHM6Q3VARW51MjAyMQ==",
                        //     ),
                        // ));

                        // $response = curl_exec($curl);
                        // $err = curl_error($curl);
                        // curl_close($curl);

                        MessageLog::create([
                            'message_id' => $msg->id,
                            'member_id' => $the_member->unique_id,
                            'phone' => $phone,
                        ]);
                    }
                }
            }
        }
    }
    return ['message' => "Success"];
}