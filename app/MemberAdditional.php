<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class MemberAdditional extends Model
{
    protected $table = 'default_esc_member_additional_details';

    protected $fillable = [
        'member_id', 'kin_name', 'kin_phone_1', 'kin_phone_2', 'kin_relationship', 'kin_address', 'beneficiary_address', 'beneficiary_name', 'beneficiary_phone_1', 'beneficiary_phone_2', 'beneficiary_relationship', 'sponsor_1', 'sponsor_2'
    ];
}