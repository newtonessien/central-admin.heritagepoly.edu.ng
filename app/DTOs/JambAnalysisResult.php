<?php

namespace App\DTOs;

class JambAnalysisResult
{
    public function __construct(
        public int $totalRecords,
        public int $totalCourses,
        public array $autoMatched,
        public array $manualReview,
        public int $eligibleRecords = 0,
        public int $belowCutoffRecords = 0,
    ) {
    }

    public function toArray(): array
    {
        return [

            'total_records' =>
                $this->totalRecords,

            'total_courses' =>
                $this->totalCourses,

            'auto_matched' =>
                $this->autoMatched,

            'manual_review' =>
                $this->manualReview,

            'auto_matched_count' =>
                count($this->autoMatched),

            'manual_review_count' =>
                count($this->manualReview),

            'eligible_records' =>
                $this->eligibleRecords,

            'below_cutoff_records' =>
                $this->belowCutoffRecords,
        ];
    }
}
