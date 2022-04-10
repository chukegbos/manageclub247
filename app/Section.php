<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Member;
use App\MemberSection;

class Section extends Model
{
    protected $table = 'default_esc_sections';

    protected $fillable = [
        'title', 'slug', 'captain', 'phone', 'captain_image', 'email', 'description', 'image', 'status', 'members'
    ];

    public function getMembersAttribute()
    {
        $id = $this->attributes['id'];
        return MemberSection::where('section_id', $id)->count();

    }
}
