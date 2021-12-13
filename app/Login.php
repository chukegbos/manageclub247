<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Login extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'logout', 'user_id', 'status', 'verified_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'deleted_at', 
    ];

    public function getVerifiedByAttribute()
    {
        $id = $this->attributes['verified_by'];
        
      
        $user = User::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->name;
        }
        else{
            return null;
        }
    }
}