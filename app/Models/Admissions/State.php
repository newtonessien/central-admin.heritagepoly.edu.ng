<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $connection = 'admissions';

    protected $table = 'states';
}
