<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionAudit extends Model
{
    protected $fillable = [
        'candidate_id',
        'regno',

        'acad_session_id',
        'program_type_id',
        'application_type_id',
        'faculty_id',
        'department_id',
        'program_id',

        'action',
        'decision',
        'reason',

        'performed_by',
        'performed_at',

        'metadata',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'performed_at' => 'datetime',
    ];

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
