<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class JambCourseAlias extends Model
{
    protected $connection = 'admissions';

    protected $table = 'jamb_course_aliases';

    protected $fillable = [
        'jamb_course_name',
        'program_name',
    ];
}
