<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $connection = 'admissions';

    protected $table = 'faculties';
}
