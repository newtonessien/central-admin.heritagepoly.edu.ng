<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{

    protected $fillable = [
    'jamb_no',
    'jamb_score',

    'program_id',
    'department_id',
    'faculty_id',

    'application_type_id',

    'state_id',
    'lga_id',
];
    protected $connection = 'admissions';

    protected $table = 'candidates';


}
