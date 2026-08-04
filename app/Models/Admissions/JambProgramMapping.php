<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class JambProgramMapping extends Model
{
    protected $connection = 'admissions';

    protected $table = 'jamb_program_mappings';

    protected $fillable = [
        'jamb_course_name',
        'program_id',
        'matching_source',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}
