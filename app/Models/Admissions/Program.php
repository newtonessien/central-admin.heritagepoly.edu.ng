<?php

namespace App\Models\Admissions;

use App\Models\Admissions\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $connection = 'admissions';

    protected $table = 'programs';

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
