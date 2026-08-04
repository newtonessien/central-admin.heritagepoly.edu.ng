<?php

namespace App\Services\JambDataImport;

use Exception;
use App\DTOs\JambAnalysisResult;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Models\Admissions\JambProgramMapping;

class ExcelAnalysisService
{
public function __construct(
protected CourseMatchingService $matcher
) {
}

public function analyze(
string $filePath
): JambAnalysisResult {

$startedAt = microtime(true);

$reader = IOFactory::createReaderForFile(
$filePath
);

$reader->setReadDataOnly(true);

$spreadsheet = $reader->load(
$filePath
);

$worksheet = $spreadsheet->getActiveSheet();

$highestRow = $worksheet->getHighestRow();

$highestColumn = $worksheet->getHighestColumn();

$header = $worksheet->rangeToArray(
"A1:{$highestColumn}1",
null,
true,
true,
false
)[0];

$header = array_map(
fn ($value) => trim((string) $value),
$header
);

$courseIndex = array_search(
'CO_NAME',
$header
);

$scoreIndex = array_search(
'RG_AGGREGATE',
$header
);

if ($scoreIndex === false) {

throw new \Exception(
'RG_AGGREGATE column not found.'
);
}

if ($courseIndex === false) {

throw new Exception(
'CO_NAME column not found.'
);
}

$courseColumn = Coordinate::stringFromColumnIndex(
$courseIndex + 1
);

$scoreColumn = Coordinate::stringFromColumnIndex(
$scoreIndex + 1
);

$eligibleRecords = 0;

$belowCutoffRecords = 0;

$cutoff = config(
'admissions.minimum_jamb_score',
150
);

$courses = [];


for (
$row = 2;
$row <= $highestRow;
$row++
) {

$course = trim(
(string) $worksheet
->getCell(
$courseColumn . $row
)
->getValue()
);

if ($course !== '') {

$courses[$course] = true;
}

$score = (int) trim(
(string) $worksheet
->getCell(
$scoreColumn . $row
)
->getValue()
);



/*
|--------------------------------------------------------------------------
| Direct Entry
|--------------------------------------------------------------------------
|
| RG_AGGREGATE = 0
|
*/

if ($score === 0) {

$eligibleRecords++;
continue;
}

/*
|--------------------------------------------------------------------------
| UTME
|--------------------------------------------------------------------------
*/

if ($score >= $cutoff) {

$eligibleRecords++;

} else {

$belowCutoffRecords++;

}
}

$spreadsheet->disconnectWorksheets();

unset($worksheet);
unset($spreadsheet);

$result = $this->analyzeCourses(
    courses: array_keys($courses),
    totalRecords: max(0, $highestRow - 1),
    eligibleRecords: $eligibleRecords,
    belowCutoffRecords: $belowCutoffRecords,
);

logger('JAMB Analysis Completed', [
'records' => $highestRow - 1,
'courses' => count($courses),
'auto_matched' => count($result->autoMatched),
'manual_review' => count($result->manualReview),
'eligible_records' => $result->eligibleRecords,
'below_cutoff_records' => $result->belowCutoffRecords,
'duration_seconds' => round(
microtime(true) - $startedAt,
2
),
]);

return $result;
}

protected function analyzeCourses(
array $courses,
int $totalRecords,
    int $eligibleRecords,
    int $belowCutoffRecords
): JambAnalysisResult {

$autoMatched = [];

$manualReview = [];

foreach ($courses as $course) {

$existingMapping =
JambProgramMapping::query()
->where(
'jamb_course_name',
$course
)
->first();

if ($existingMapping) {

$autoMatched[] = [
'course' => $course,
'program_id' => $existingMapping->program_id,
'source' => 'existing_mapping',
];

continue;
}

$match = $this->matcher->match(
$course
);

if ($match['matched']) {

$autoMatched[] = [
'course' => $course,

'program_id' =>
$match['program']->id,

'program_name' =>
$match['program']->name,

'source' =>
$match['matching_source'],
];

continue;
}

$manualReview[] = [
'course' => $course,
];
}



return new JambAnalysisResult(
totalRecords: $totalRecords,
totalCourses: count($courses),
autoMatched: $autoMatched,
manualReview: $manualReview,
eligibleRecords: $eligibleRecords,
belowCutoffRecords: $belowCutoffRecords,
);
}
}
