<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\Message;
use App\MessageLog;

class MessageLog extends Model
{
    use SoftDeletes;
    protected $table = 'message_logs';

    protected $fillable = [
        'message_id', 'phone', 'status', 'member_id'
    ];

    protected $dates = [
        'deleted_at', 'updated_at'
    ];
}
