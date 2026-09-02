<?php

namespace App\Services;

use App\Models\AdmissionAudit;
use Illuminate\Support\Facades\Auth;

class AdmissionAuditService
{
    public function record(
        array $candidate,
        string $action,
        ?string $decision = null,
        ?string $reason = null,
        array $metadata = [],
    ): AdmissionAudit {
        return AdmissionAudit::create([
            'candidate_id'       => $candidate['id'] ?? null,
            'regno'              => $candidate['regno'] ?? null,

            'acad_session_id'    => $candidate['acad_session_id'] ?? null,
            'program_type_id'    => $candidate['program_type_id'] ?? null,
            'application_type_id'=> $candidate['application_type_id'] ?? null,
            'faculty_id'         => $candidate['faculty_id'] ?? null,
            'department_id'      => $candidate['department_id'] ?? null,
            'program_id'         => $candidate['program_id'] ?? null,

            'action'             => $action,
            'decision'           => $decision,
            'reason'             => $reason,

            'performed_by'       => Auth::id(),
            'performed_at'       => now(),

            'metadata'           => $metadata,
        ]);
    }
}
