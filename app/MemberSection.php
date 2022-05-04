<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Role;
use App\Member;
use App\User;

class MemberSection extends Model
{
    protected $table = 'default_esc_member_sections';
    protected $fillable = [
        'member_id', 'section_id', 'member'
    ];

    public function getMemberIdAttribute()
    {
        $id = $this->attributes['member_id'];
        $member = Member::find($id);
        if ($member) {
            $user = User::where('unique_id', $member->membership_id)->first();
            if ($user) {
                return $user;
            }
            else {
                return '';
            }
        }
        else {
            return '';
        }
    }
}
