<?php

namespace App\Services\JambDataImport;

use App\Models\Admissions\JambProgramMapping;
use App\Models\Admissions\Program;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class JambMappingService
{
    /**
     * Save automatically detected mappings.
     */
    public function saveAutoMappings(
        array $autoMatched
    ): int {

        return DB::connection('admissions')
            ->transaction(function () use ($autoMatched) {

                $count = 0;

                foreach ($autoMatched as $match) {

                    if (empty($match['program_id'])) {
                        continue;
                    }

                    JambProgramMapping::updateOrCreate(
                        [
                            'jamb_course_name' => trim(
                                $match['course']
                            ),
                        ],
                        [
                            'program_id' => $match['program_id'],

                            'matching_source' =>
                                $match['source']
                                ?? 'exact',
                        ]
                    );

                    $count++;
                }

                return $count;
            });
    }

    /**
     * Return courses requiring manual review.
     */
    public function unresolvedCourses(
        array $manualReview
    ): array {

        return collect($manualReview)
            ->pluck('course')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Create or update a single manual mapping.
     */
    public function createManualMapping(
        string $jambCourseName,
        int $programId
    ): JambProgramMapping {

        $program = Program::query()
            ->where('program_type_id', 1)
            ->where('is_visible', 1)
            ->findOrFail($programId);

        return JambProgramMapping::updateOrCreate(
            [
                'jamb_course_name' => trim(
                    $jambCourseName
                ),
            ],
            [
                'program_id' => $program->id,

                'matching_source' => 'manual',
            ]
        );
    }

    /**
     * Save multiple manual mappings.
     */
    public function saveManualMappings(
        array $mappings
    ): int {

        return DB::connection('admissions')
            ->transaction(function () use ($mappings) {

                $count = 0;

                foreach ($mappings as $mapping) {

                    if (
                        empty($mapping['course'])
                        || empty($mapping['program_id'])
                    ) {
                        continue;
                    }

                    $this->createManualMapping(
                        $mapping['course'],
                        (int) $mapping['program_id']
                    );

                    $count++;
                }

                return $count;
            });
    }

    /**
     * Programs available for mapping.
     */
    public function mappingPrograms(): Collection
    {
        return Program::query()
            ->with('department')
            ->where('program_type_id', 1)
            ->where('is_visible', 1)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'department_id',
            ]);
    }

    /**
     * All existing mappings.
     */
    public function allMappings(): Collection
    {
        return JambProgramMapping::with(
            'program.department.faculty'
        )
        ->orderBy('jamb_course_name')
        ->get();
    }

    /**
     * Mapping statistics.
     */
    public function statistics(): array
    {
        return [

            'total_mappings' =>
                JambProgramMapping::count(),

            'exact' =>
                JambProgramMapping::where(
                    'matching_source',
                    'exact'
                )->count(),

            'alias' =>
                JambProgramMapping::where(
                    'matching_source',
                    'alias'
                )->count(),

            'education_transform' =>
                JambProgramMapping::where(
                    'matching_source',
                    'education_transform'
                )->count(),

            'manual' =>
                JambProgramMapping::where(
                    'matching_source',
                    'manual'
                )->count(),
        ];
    }
}
