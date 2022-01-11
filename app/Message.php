<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\Message;
use App\MessageLog;

class Message extends Model
{
    use SoftDeletes;
    protected $table = 'messages';

    protected $fillable = [
        'user_id', 'message', 'sender_name', 'page_count', 'defer_date', 'status'
    ];

    protected $dates = [
        'deleted_at', 'updated_at'
    ];

    public function getUserIdAttribute()
    {
        $id = $this->attributes['user_id'];
        $user = User::where('deleted_at', NULL)->find($id);
        if ($user) {
            return $user->name;
        }
        else {
            return NULL;
        }
    }
}
