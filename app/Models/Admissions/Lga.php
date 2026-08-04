<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class Lga extends Model
{
    protected $connection = 'admissions';

    protected $table = 'lgas';


    public function state()
{
    return $this->belongsTo(State::class);
}
}
