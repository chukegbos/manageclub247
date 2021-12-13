<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Role;

class MemberType extends Model
{
    protected $table = 'default_esc_member_types';
    protected $fillable = [
        'title', 'system'
    ];
}
