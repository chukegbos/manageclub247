<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\MemberType;

class Member extends Model
{
    protected $table = 'default_esc_members';

    protected $fillable = [
        'last_name', 'first_name', 'middle_name', 'email', 'phone_1', 'phone_2', 'state', 'address', 'city', 'country', 'office_address', 'lga', 'state_of_origin',  'membership_id', 'home_town', 'marital_status', 'spouse_name', 'children', 'member_type', 'get_state_of_origin', 'get_lga', 'get_state', 'get_city', 'get_member_type'
    ];

    public function getGetMemberTypeAttribute()
    {
        $member_type = $this->attributes['member_type'];
        $type = MemberType::find($member_type);
        if ($type) {
            return $type->title;   
        }
        else{
            return 'None';
        }
    }

    public function getGetStateOfOriginAttribute()
    {
        $id = $this->attributes['state_of_origin'];
        if (!$id) {
            return null;
        }
        $state = DB::table('states')->where('id' ,$id)->first();
        return $state->title;
    }

    public function getGetLgaAttribute()
    {
        $id = $this->attributes['lga'];
        if (!$id) {
            return null;
        }
        $city = DB::table('local_governments')->where('id' ,$id)->first();
        return $city->name;
    }

    public function getGetStateAttribute()
    {
        $id = $this->attributes['state_of_origin'];
        if (!$id) {
            return null;
        }
        $state = DB::table('states')->where('id' ,$id)->first();
        return $state->title;
    }

    public function getGetCityAttribute()
    {
        $id = $this->attributes['lga'];
        if (!$id) {
            return null;
        }
        $city = DB::table('local_governments')->where('id' ,$id)->first();
        return $city->name;
    }


}
