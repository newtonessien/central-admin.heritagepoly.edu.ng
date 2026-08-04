<?php

namespace App\Services\JambDataImport;

use App\Models\Admissions\Program;
use App\Models\Admissions\JambCourseAlias;
use App\Models\Admissions\JambImportBatch;
use App\Models\Admissions\JambProgramMapping;

class AdmissionsService
{
    public function undergraduatePrograms()
    {
        return Program::with(
            'department.faculty'
        )
        ->where('program_type_id', 1)
        ->where('is_visible', 1)
        ->orderBy('name')
        ->get();
    }

    public function getProgram(int $id): ?Program
    {
        return Program::with(
            'department.faculty'
        )->find($id);
    }

    public function aliases()
    {
        return JambCourseAlias::all();
    }

    public function mappings()
    {
        return JambProgramMapping::with(
            'program.department.faculty'
        )->get();
    }

    public function createBatch(
        array $data
    ): JambImportBatch {

        return JambImportBatch::create($data);
    }

    public function getBatch(
        int $id
    ): ?JambImportBatch {

        return JambImportBatch::find($id);
    }

    public function updateBatch(
        int $batchId,
        array $data
    ): bool {

        return JambImportBatch::where(
            'id',
            $batchId
        )->update($data);
    }
}
