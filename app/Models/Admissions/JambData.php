<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class JambData extends Model
{

protected $fillable = [

    'jamb_no',
    'jamb_score',

    'last_name',
    'first_name',
    'other_names',

    'gender',

    'state',
    'lga',

    'state_id',
    'lga_id',

    'course',
    'course_id',

    'department_id',
    'faculty_id',

    'application_type_id',
    'user_id',
];
    protected $connection = 'admissions';

    protected $table = 'jamb_data';
}
