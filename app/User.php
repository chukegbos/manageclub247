<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Member;
use App\MemberCard;
use App\Store;
use App\Role;
use App\User;
use App\Suspend;
use App\Death;
use App\PaymentDebit;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes, Notifiable;

    protected $fillable = [
        'card_numbers', 'name', 'email', 'phone', 'password', 'role', 'address', 'store', 'sale_percent', 'next_of_kin_address', 'next_of_kin_phone', 'next_of_kin', 'state', 'city', 'image', 'unique_id', 'salary', 'credit_unit', 'wallet_balance', 'c_person', 'pin', 'invoice', 'entrance_date', 'dob', 'gender', 'door_access', 'access', 'approved_by', 'approved', 'member_type', 'get_role', 'debt', 'bar_wallet'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = [
        'deleted_at', 
    ];

    public function getGetRoleAttribute()
    {
        $id = $this->attributes['role'];
        $role = DB::table('roles')->where('id', $id)->first();
        return $role;
    }
    
    public function getCardNumbersAttribute()
    {
        $id = $this->attributes['unique_id'];
        if ($id) {
            $member = Member::where('membership_id', $id)->first();
            if ($member) {
                
                $member_card = MemberCard::where('member_id', $member->id)->get();
               return $member_card;
            }
            else {
                return null;
            }
            
        }
        else {
            return null;
        }
    }

    public function getCPersonAttribute()
    {
        $id = $this->attributes['unique_id'];
        if ($id) {
            return Member::where('membership_id', $id)->first();
        }
        else {
            return null;
        }
    }

    public function getDebtAttribute()
    {
        $id = $this->attributes['unique_id'];
        if ($id) {
            $member = Member::where('membership_id', $id)->first();
            if ($member) {
                $debt = PaymentDebit::where('deleted_at', NULL)->where('member_id', $member->id)->where('status', 0)->sum('amount');
                return $debt;
            }
            else 
            {
                return null;
            }

        }
        else 
        {
            return null;
        }
    }

    public function getMemberTypeAttribute()
    {
        $id = $this->attributes['unique_id'];
        if ($id) {
            $member = Member::where('membership_id', $id)->first();
            if ($member) {
               return $member->member_type;
            }
            else {
                return null;
            }
            
        }
        else {
            return null;
        }
    }

    public function getStateAttribute()
    {
        $id = $this->attributes['state'];
        if (!$id) {
            return null;
        }
        $state = DB::table('states')->where('id' ,$id)->first();
        return $state->title;
    }

    public function getCityAttribute()
    {
        $id = $this->attributes['city'];
        if (!$id) {
            return null;
        }
        $city = DB::table('local_governments')->where('id' ,$id)->first();
        if ($city) {
            return $city->name;
        }
        return null;
    }

    public function getStoreAttribute()
    {
        $id = $this->attributes['store'];
        if ($id) {
            $store = Store::where('deleted_at', NULL)->find($id);
            if ($store) {
                return $store->name;
            }
            else{
                return "---";
            }
        }
        else{
            return "---";
        }
    }

    public function getAccessAttribute()
    {
        $door_access = $this->attributes['door_access'];
        $unique_id = $this->attributes['unique_id'];
        if ($door_access==0) {
            $approved = User::where('deleted_at', NULL)->where('role', 0)->where('approved', 0)->where('unique_id', $unique_id)->first();
            $phone = User::where('deleted_at', NULL)->where('role', 0)->where('phone', NULL)->where('unique_id', $unique_id)->first();
            $suspend = Suspend::where('deleted_at', NULL)->where('status', 0)->where('membership_id', $unique_id)->first();
            $death = Death::where('deleted_at', NULL)->where('member_id', $unique_id)->first();
            $debit = PaymentDebit::where('deleted_at', NULL)->where('member_id', $unique_id)->where('period', 0)->first();

            if ($death) {
                return 'Deceased';
            }
            elseif ($approved) {
                return 'Pending Approval';
            }
            elseif ($phone) {
                return 'No phone number';
            }
            
            elseif ($suspend) {
                return 'Suspended';
            }

             elseif ($phone) {
                return 'No phone number';
            }
            
            elseif ($debit) {
                return 'In Debt';
            }
        }
    }

    public function getApprovedByAttribute()
    {
        $id = $this->attributes['approved_by'];
        if ($id) {
            $user = User::Find($id);
            return $user->name;
        }
        else {
            return null;
        }
    }
}
