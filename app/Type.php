<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Role;

class Type extends Model
{
    protected $table = 'default_esc_member_types';
    protected $fillable = [
        'title', 'system'
    ];

    public function product()
    {
        return $this->belongsToMany('App\Product');
    }
}
