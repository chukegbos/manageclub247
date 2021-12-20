<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fund extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'mop', 'user_id', 'customer_id', 'account', 'ref_id', 'tran_type', 'approved_by', 'store_id', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'remember_token', 'deleted_at', 
    ];

    public function getCustomerIdAttribute()
    {
        $id = $this->attributes['customer_id'];
        if ($id) {
            $user = Member::where('deleted_at', NULL)->find($id);
            if ($user) {
                return $user;
            }
            else{
                return '---';
            }
        }
        else{
            return '---';
        }
    }

    public function getApprovedByAttribute()
    {
        $id = $this->attributes['approved_by'];
        if ($id) {
            $user = User::where('deleted_at', NULL)->find($id);
            if ($user) {
                return $user->name;
            }
            else{
                return '---';
            }
        }
        else{
            return '---';
        }
    }

    public function getUserIdAttribute()
    {
        $user_id = $this->attributes['user_id'];
        if ($user_id) {
            $user = User::where('deleted_at', NULL)->find($user_id);
            if ($user) {
                return $user->name;
            }
            else{
                return '---';
            }
        }
        else{
            return '---';
        }
    }

    public function getStatusAttribute()
    {
        $status = $this->attributes['status'];
        if ($status==1) {
            return 'Pending';
        }
        elseif($status==0){
            return 'Rejected';
        }

        else{
            return 'Approved';
        }
    }
}