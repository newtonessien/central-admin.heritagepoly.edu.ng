<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class JambImportBatch extends Model
{
    protected $connection = 'admissions';

    protected $table = 'jamb_import_batches';

    protected $fillable = [
        'filename',
        'admission_year',
        'uploaded_by',
        'total_records',
        'imported_records',
        'failed_records',
        'candidate_updates',
        'status',
        'started_at',
        'completed_at',
        'remarks',
    ];

       protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
