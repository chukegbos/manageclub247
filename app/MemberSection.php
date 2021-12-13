<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Role;

class MemberSection extends Model
{
    protected $table = 'default_esc_member_sections';
    protected $fillable = [
        'member_id', 'section_id',
    ];
}
