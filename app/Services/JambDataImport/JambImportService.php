<?php

namespace App\Services\JambDataImport;

use App\Models\Admissions\JambImportBatch;
use App\Models\Admissions\JambProgramMapping;
use App\Services\JambDataImport\CandidateSyncService;
use Exception;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class JambImportService
{
    protected array $courseMappings = [];

    protected int $batchSize = 1000;

    public function __construct(
        protected NameParserService $nameParser,
        protected StateLgaResolverService $resolver,
        protected CandidateSyncService $candidateSync
    ) {
        $this->loadMappings();
    }

    protected function loadMappings(): void
    {
        $mappings = JambProgramMapping::query()
            ->with('program.department')
            ->get();

        foreach ($mappings as $mapping) {

            if (!$mapping->program) {
                continue;
            }

            $this->courseMappings[
                strtoupper(
                    trim($mapping->jamb_course_name)
                )
            ] = [

                'program_id' =>
                    $mapping->program->id,

                'department_id' =>
                    $mapping->program->department_id,

                'faculty_id' =>
                    optional(
                        $mapping->program->department
                    )->faculty_id,
            ];
        }
    }

    public function import(
        string $filePath,
        int $batchId
    ): array {

        $startedAt = microtime(true);

        $batch = JambImportBatch::findOrFail(
            $batchId
        );

        $batch->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        DB::connection('admissions')
        ->table('jamb_data')
        ->truncate();

        $cutoff = (int) config(
            'admissions.minimum_jamb_score',
            150
        );

        $reader = IOFactory::createReaderForFile(
            $filePath
        );

        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load(
            $filePath
        );

        $worksheet =
            $spreadsheet->getActiveSheet();

        $highestRow =
            $worksheet->getHighestRow();

        $highestColumn =
            $worksheet->getHighestColumn();

        $header = $worksheet->rangeToArray(
            "A1:{$highestColumn}1",
            null,
            true,
            true,
            false
        )[0];

        $header = array_map(
            fn ($value) => trim(
                (string) $value
            ),
            $header
        );

        $columns = $this->mapColumns(
            $header
        );

        $imported = 0;

        $failed = 0;

        $skippedCutoff = 0;

        $batchRecords = [];

        for (
            $row = 2;
            $row <= $highestRow;
            $row++
        ) {

            try {

                $record = $this->buildImportRecord(
                    worksheet: $worksheet,
                    row: $row,
                    columns: $columns,
                    cutoff: $cutoff
                );

                if ($record === null) {

                    $skippedCutoff++;

                    continue;
                }

                $batchRecords[] = $record;

                $imported++;

                if (
                    count($batchRecords)
                    >= $this->batchSize
                ) {

                    $this->upsertBatch(
                        $batchRecords
                    );

                    $batchRecords = [];
                }

                if ($row % 1000 === 0) {

                    $batch->update([
                        'imported_records' =>
                            $imported,

                        'failed_records' =>
                            $failed,
                    ]);
                }

            } catch (Exception $e) {

                $failed++;

                logger()->error(
                    'JAMB Import Row Failed',
                    [
                        'row' => $row,
                        'message' =>
                            $e->getMessage(),
                    ]
                );
            }
        }

        if (!empty($batchRecords)) {

            $this->upsertBatch(
                $batchRecords
            );
        }

        $candidatesUpdated =
    $this->candidateSync->sync();

        $spreadsheet
            ->disconnectWorksheets();

        unset($worksheet);
        unset($spreadsheet);

        $batch->update([
            'status' => 'completed',

            'completed_at' => now(),

            'total_records' =>
                max(0, $highestRow - 1),

            'imported_records' =>
                $imported,

            'failed_records' =>
                $failed,

            'remarks' =>
                "Skipped cutoff: {$skippedCutoff}. Candidate updates: {$candidatesUpdated}",
        ]);

        logger()->info(
            'JAMB Import Completed',
            [
                'total_records' =>
                    $highestRow - 1,

                'imported_records' =>
                    $imported,

                'failed_records' =>
                    $failed,

                'skipped_cutoff' =>
                    $skippedCutoff,

                'duration_seconds' =>
                    round(
                        microtime(true)
                        - $startedAt,
                        2
                    ),
            ]
        );

        return [

            'total_records' =>
                $highestRow - 1,

            'imported_records' =>
                $imported,

            'failed_records' =>
                $failed,

            'skipped_cutoff' =>
                $skippedCutoff,

            'candidates_updated' =>
                $candidatesUpdated,
        ];
    }

    protected function buildImportRecord(
        $worksheet,
        int $row,
        array $columns,
        int $cutoff
    ): ?array {

        $jambNo = $this->cell(
            $worksheet,
            $columns['jamb_no'],
            $row
        );

        if (!$jambNo) {
            return null;
        }

        $score = (int) $this->cell(
            $worksheet,
            $columns['score'],
            $row
        );

        /*
        |--------------------------------------------------------------------------
        | Direct Entry = 0
        | UTME below cutoff = skip
        |--------------------------------------------------------------------------
        */

        if (
            $score > 0 &&
            $score < $cutoff
        ) {
            return null;
        }

        $courseName = $this->cell(
            $worksheet,
            $columns['course'],
            $row
        );

        $mapping =
            $this->courseMappings[
                strtoupper(
                    trim($courseName)
                )
            ] ?? null;

        if (!$mapping) {

            throw new Exception(
                "No mapping found for {$courseName}"
            );
        }

        $parsedName =
            $this->nameParser->parse(
                $this->cell(
                    $worksheet,
                    $columns['name'],
                    $row
                )
            );

        $stateName = $this->cell(
            $worksheet,
            $columns['state'],
            $row
        );

        $lgaName = $this->cell(
            $worksheet,
            $columns['lga'],
            $row
        );

        $location =
            $this->resolver->resolve(
                $stateName,
                $lgaName
            );

        return [

            'jamb_no' => $jambNo,

            'jamb_score' => $score,

            'last_name' =>
                $parsedName['last_name'],

            'first_name' =>
                $parsedName['first_name'],

            'other_names' =>
                $parsedName['other_names'],

            'gender' => $this->cell(
                $worksheet,
                $columns['gender'],
                $row
            ),

            'state' => $stateName,

            'lga' => $lgaName,

            'state_id' =>
                $location['state_id'],

            'lga_id' =>
                $location['lga_id'],

            'course' => $courseName,

            'course_id' =>
                $mapping['program_id'],

            'department_id' =>
                $mapping['department_id'],

            'faculty_id' =>
                $mapping['faculty_id'],

            'application_type_id' =>
                $score > 0 ? 1 : 3,

            'created_at' => now(),

            'updated_at' => now(),
        ];
    }

    protected function upsertBatch(
        array $records
    ): void {

        DB::connection('admissions')
            ->table('jamb_data')
            ->upsert(

                $records,

                [
                    'jamb_no'
                ],

                [
                    'jamb_score',
                    'last_name',
                    'first_name',
                    'other_names',
                    'gender',
                    'state',
                    'lga',
                    'state_id',
                    'lga_id',
                    'course',
                    'course_id',
                    'department_id',
                    'faculty_id',
                    'application_type_id',
                    'updated_at',
                ]
            );
    }

    protected function cell(
        $worksheet,
        string $column,
        int $row
    ): string {

        return trim(
            (string) $worksheet
                ->getCell(
                    $column . $row
                )
                ->getValue()
        );
    }

    protected function mapColumns(
        array $header
    ): array {

        return [

            'jamb_no' =>
                Coordinate::stringFromColumnIndex(
                    array_search(
                        'RG_NUM',
                        $header
                    ) + 1
                ),

            'name' =>
                Coordinate::stringFromColumnIndex(
                    array_search(
                        'RG_CANDNAME',
                        $header
                    ) + 1
                ),

            'gender' =>
                Coordinate::stringFromColumnIndex(
                    array_search(
                        'RG_SEX',
                        $header
                    ) + 1
                ),

            'state' =>
                Coordinate::stringFromColumnIndex(
                    array_search(
                        'STATE_NAME',
                        $header
                    ) + 1
                ),

            'lga' =>
                Coordinate::stringFromColumnIndex(
                    array_search(
                        'LGA_NAME',
                        $header
                    ) + 1
                ),

            'score' =>
                Coordinate::stringFromColumnIndex(
                    array_search(
                        'RG_AGGREGATE',
                        $header
                    ) + 1
                ),

            'course' =>
                Coordinate::stringFromColumnIndex(
                    array_search(
                        'CO_NAME',
                        $header
                    ) + 1
                ),
        ];
    }
}
