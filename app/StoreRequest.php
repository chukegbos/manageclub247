<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;

class StoreRequest extends Model
{
    use SoftDeletes;
    protected $table = 'store_request';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'req_id', 'product_id', 'store_id', 'qty', 'sender_id', 'reciever_id', 'approved_by', 'status'
    ];

    public function getSenderIdAttribute()
    {
        $id = $this->attributes['sender_id'];
        $sender_id = User::where('deleted_at', NULL)->find($id);
        if ($sender_id) {
            return $sender_id->name;
        }
    }

    public function getRecieverIdAttribute()
    {
        $id = $this->attributes['reciever_id'];
        $reciever_id = User::where('deleted_at', NULL)->find($id);
        if ($reciever_id) {
            return $reciever_id->name;
        }
    }

    public function getApprovedByAttribute()
    {
        $id = $this->attributes['approved_by'];
        $approved_by = User::where('deleted_at', NULL)->find($id);
        if ($approved_by) {
            return $approved_by->name;
        }
    }

    protected $dates = [
        'deleted_at', 
    ];
}
