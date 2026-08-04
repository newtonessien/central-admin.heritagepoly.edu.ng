<?php

namespace App\Services\JambDataImport;

use App\Models\Admissions\Candidate;
use App\Models\Admissions\JambData;

class CandidateSyncService
{
    public function sync(): int
    {
        $updated = 0;

        JambData::query()
            ->select([
                'id',
                'jamb_no',
                'jamb_score',
                'course_id',
                'department_id',
                'faculty_id',
                'application_type_id',
                //'state_id',
                //'lga_id',
            ])
            ->chunkById(1000, function ($records) use (&$updated) {

                $jambNumbers = $records
                    ->pluck('jamb_no')
                    ->filter()
                    ->values();

                $candidates = Candidate::query()
                    ->whereIn('jamb_no', $jambNumbers)
                    ->get()
                    ->keyBy('jamb_no');

                foreach ($records as $record) {

                    $candidate = $candidates[
                        $record->jamb_no
                    ] ?? null;

                    if (!$candidate) {
                        continue;
                    }

                    if (
                        $this->updateCandidate(
                            $candidate,
                            $record
                        )
                    ) {
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    protected function updateCandidate(
        Candidate $candidate,
        JambData $jambData
    ): bool {

        $changes = [];

        if (
            (int) $candidate->program_id
            !== (int) $jambData->course_id
        ) {
            $changes['program_id']
                = $jambData->course_id;
        }

        if (
            (int) $candidate->department_id
            !== (int) $jambData->department_id
        ) {
            $changes['department_id']
                = $jambData->department_id;
        }

        if (
            (int) $candidate->faculty_id
            !== (int) $jambData->faculty_id
        ) {
            $changes['faculty_id']
                = $jambData->faculty_id;
        }

        if (
            (int) $candidate->application_type_id
            !== (int) $jambData->application_type_id
        ) {
            $changes['application_type_id']
                = $jambData->application_type_id;
        }

        if (
            (string) $candidate->jamb_score
            !== (string) $jambData->jamb_score
        ) {
            $changes['jamb_score']
                = $jambData->jamb_score;
        }

        /**
         * Optional:
         * update state/lga only if empty
         */
        // if (
        //     empty($candidate->state_id)
        //     && !empty($jambData->state_id)
        // ) {
        //     $changes['state_id']
        //         = $jambData->state_id;
        // }

        // if (
        //     empty($candidate->lga_id)
        //     && !empty($jambData->lga_id)
        // ) {
        //     $changes['lga_id']
        //         = $jambData->lga_id;
        // }

        if (empty($changes)) {
            return false;
        }

        $candidate->update($changes);

        return true;
    }
}
