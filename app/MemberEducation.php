<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class MemberEducation extends Model
{
    protected $table = 'default_esc_member_educational_details';

    protected $fillable = [
        'member_id', 'level', 'institution', 'degree'
    ];
}