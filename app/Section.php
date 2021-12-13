<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Role;

class Section extends Model
{
    protected $table = 'default_esc_sections';

    protected $fillable = [
        'title', 'slug', 'captain', 'phone', 'captain_image', 'email', 'description', 'image', 'status'
    ];
}
