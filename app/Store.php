<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;


class Store extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'manager', 'address', 'target', 'stock_limit', 'email', 'phone', 'group_bar'
    ];

    public function getManagerAttribute()
    {
        $id = $this->attributes['id'];
        $user = User::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->name;
        }
    }

    protected $dates = [
        'deleted_at', 
    ];

    public function user()
    {
        return $this->belongsToMany('App\User');
    }

    public function inventory()
    {
        return $this->belongsToMany('App\Inventory');
    }
}
