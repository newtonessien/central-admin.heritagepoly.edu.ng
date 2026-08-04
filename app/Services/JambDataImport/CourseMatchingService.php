<?php

namespace App\Services\JambDataImport;

use App\Models\Admissions\JambCourseAlias;
use App\Models\Admissions\Program;

class CourseMatchingService
{
    /**
     * @var array<string, Program>
     */
    protected array $programLookup = [];

    /**
     * @var array<string, string>
     */
    protected array $aliasLookup = [];

    public function __construct()
    {
        $programs = Program::query()
            ->where('program_type_id', 1)
            ->where('is_visible', 1)
            ->get();

        foreach ($programs as $program) {

            $this->programLookup[
                $this->normalize($program->name)
            ] = $program;
        }

        foreach (JambCourseAlias::all() as $alias) {

            $this->aliasLookup[
                $this->normalize($alias->jamb_course_name)
            ] = $alias->program_name;
        }
    }

    /**
     * Match a JAMB course to a Program.
     */
    public function match(string $jambCourse): array
    {
        $normalizedCourse = $this->normalize($jambCourse);

        /**
         * EXACT MATCH
         */
        if (isset($this->programLookup[$normalizedCourse])) {

            return [
                'matched' => true,
                'program' => $this->programLookup[$normalizedCourse],
                'matching_source' => 'exact',
            ];
        }

        /**
         * ALIAS MATCH
         */
        if (isset($this->aliasLookup[$normalizedCourse])) {

            $programName = $this->aliasLookup[$normalizedCourse];

            $normalizedProgram =
                $this->normalize($programName);

            if (
                isset(
                    $this->programLookup[
                        $normalizedProgram
                    ]
                )
            ) {
                return [
                    'matched' => true,
                    'program' => $this->programLookup[$normalizedProgram],
                    'matching_source' => 'alias',
                ];
            }
        }

        /**
         * EDUCATION TRANSFORMATION
         */
        $educationProgram =
            $this->transformEducationCourse(
                $jambCourse
            );

        if ($educationProgram) {

            $normalizedEducationProgram =
                $this->normalize(
                    $educationProgram
                );

            if (
                isset(
                    $this->programLookup[
                        $normalizedEducationProgram
                    ]
                )
            ) {
                return [
                    'matched' => true,
                    'program' => $this->programLookup[
                        $normalizedEducationProgram
                    ],
                    'matching_source' => 'education_transform',
                ];
            }
        }

        return [
            'matched' => false,
            'program' => null,
            'matching_source' => null,
        ];
    }

    /**
     * Convert:
     *
     * Education & Biology
     *      =>
     * Biology Education
     */
    protected function transformEducationCourse(
        string $course
    ): ?string {

        $course = trim($course);

        if (
            stripos(
                $course,
                'Education & '
            ) === 0
        ) {
            $subject = trim(
                substr(
                    $course,
                    strlen('Education & ')
                )
            );

            return "{$subject} Education";
        }

        return null;
    }

    /**
     * Normalize values for comparison.
     */
    protected function normalize(
        string $value
    ): string {

        $value = strtoupper(trim($value));

        $value = str_replace('&', 'AND', $value);

        $value = str_replace('/', ' ', $value);

        $value = str_replace('-', ' ', $value);

        $value = preg_replace(
            '/[^A-Z0-9 ]/',
            '',
            $value
        );

        $value = preg_replace(
            '/\s+/',
            ' ',
            $value
        );

        return trim($value);
    }
}
