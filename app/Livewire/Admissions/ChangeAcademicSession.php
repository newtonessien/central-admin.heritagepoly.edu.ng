<?php

namespace App\Livewire\Admissions;

use App\Services\Clients\AdmissionsPortalClient;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ChangeAcademicSession extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    public string $regno = '';


    /*
    |--------------------------------------------------------------------------
    | Candidate
    |--------------------------------------------------------------------------
    */

    public ?array $candidate = null;


    /*
    |--------------------------------------------------------------------------
    | Current Academic Session
    |--------------------------------------------------------------------------
    */

    public ?int $currentSessionId = null;

    public ?string $currentSessionName = null;


    /*
    |--------------------------------------------------------------------------
    | New Academic Session
    |--------------------------------------------------------------------------
    */

    public ?int $acadSessionId = null;


    /*
    |--------------------------------------------------------------------------
    | Academic Sessions
    |--------------------------------------------------------------------------
    */

    public array $academicSessions = [];


    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    public bool $loadingCandidate = false;

    public bool $processing = false;

    public string $errorMessage = '';


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->loadAcademicSessions();
    }


    /*
    |--------------------------------------------------------------------------
    | Find Candidate
    |--------------------------------------------------------------------------
    */

    public function findCandidate(): void
    {

          $this->resetError();

        $this->validate([
            'regno' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $this->loadingCandidate = true;

        try {

            $client = app(AdmissionsPortalClient::class);

            /*
            |--------------------------------------------------------------------------
            | Get Candidate
            |--------------------------------------------------------------------------
            |
            | candidateByRegno() gives us the candidate IDs.
            | The display names are resolved separately below.
            |
            */

            $result = $client->candidateByRegno(
                trim($this->regno),
                'details'
            );


            /*
            |--------------------------------------------------------------------------
            | API Error
            |--------------------------------------------------------------------------
            */

            if (($result['status'] ?? null) !== 'success') {

                $this->clearCandidate();

                $this->errorMessage =
                    is_string($result['error'] ?? null)
                        ? $result['error']
                        : 'Candidate could not be found.';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Extract Candidate
            |--------------------------------------------------------------------------
            */

            $body = $result['data'] ?? null;

            $candidate = isset($body['data'])
                ? $body['data']
                : $body;

            if (!is_array($candidate)) {

                $this->clearCandidate();

                $this->errorMessage =
                    'Invalid candidate response received.';

                return;
            }

            $this->candidate = $candidate;


            /*
            |--------------------------------------------------------------------------
            | Current Academic Session
            |--------------------------------------------------------------------------
            */

            $this->currentSessionId =
                isset($this->candidate['acad_session_id'])
                    ? (int) $this->candidate['acad_session_id']
                    : null;


            /*
            |--------------------------------------------------------------------------
            | Resolve Display Names
            |--------------------------------------------------------------------------
            */

            $this->resolveCandidateNames();


            /*
            |--------------------------------------------------------------------------
            | New Selection Must Start Blank
            |--------------------------------------------------------------------------
            */

            $this->acadSessionId = null;

        } catch (\Throwable $e) {

            Log::error(
                'Change Academic Session → candidate lookup failed',
                [
                    'regno' => $this->regno,
                    'error' => $e->getMessage(),
                ]
            );

            $this->clearCandidate();

            $this->errorMessage =
                'Unable to retrieve candidate details.';

        } finally {

            $this->loadingCandidate = false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Candidate Display Names
    |--------------------------------------------------------------------------
    */

    protected function resolveCandidateNames(): void
    {
        if (!$this->candidate) {
            return;
        }

        $client = app(AdmissionsPortalClient::class);


        /*
        |--------------------------------------------------------------------------
        | Program Type
        |--------------------------------------------------------------------------
        */

        $programTypeId =
            isset($this->candidate['program_type_id'])
                ? (int) $this->candidate['program_type_id']
                : null;

        if ($programTypeId) {

            $programTypes = $client->getProgramTypes(
                $programTypeId
            );

            $programType = collect($programTypes)->first(
                fn ($item) =>
                    (int) ($item['id'] ?? 0) === $programTypeId
            );

            $this->candidate['program_type'] =
                $programType['name']
                ?? $programType['program_type']
                ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | Academic Session
        |--------------------------------------------------------------------------
        */

        $acadSessionId =
            isset($this->candidate['acad_session_id'])
                ? (int) $this->candidate['acad_session_id']
                : null;

        if ($acadSessionId) {

            /*
            | Use the already-loaded sessions first.
            */

            $session = collect($this->academicSessions)->first(
                fn ($item) =>
                    (int) ($item['id'] ?? 0) === $acadSessionId
            );

            /*
            | Fallback: reload sessions if it wasn't found.
            */

            if (!$session) {

                $sessions = $client->getAcadSessions();

                $session = collect($sessions)->first(
                    fn ($item) =>
                        (int) ($item['id'] ?? 0) === $acadSessionId
                );
            }

            if ($session) {

                $this->candidate['acadSession'] =
                    $session['name']
                    ?? $session['session_name']
                    ?? null;

                $this->currentSessionName =
                    $this->candidate['acadSession'];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Faculty
        |--------------------------------------------------------------------------
        */

        $facultyId =
            isset($this->candidate['faculty_id'])
                ? (int) $this->candidate['faculty_id']
                : null;

        if ($facultyId) {

            $faculties = $client->getFaculties();

            $faculty = collect($faculties)->first(
                fn ($item) =>
                    (int) ($item['id'] ?? 0) === $facultyId
            );

            if ($faculty) {

                $this->candidate['faculty'] =
                    $faculty['name']
                    ?? $faculty['faculty']
                    ?? null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Department
        |--------------------------------------------------------------------------
        */

        $departmentId =
            isset($this->candidate['department_id'])
                ? (int) $this->candidate['department_id']
                : null;

        if ($facultyId && $departmentId) {

            $departments = $client->getDepartments(
                $facultyId
            );

            $department = collect($departments)->first(
                fn ($item) =>
                    (int) ($item['id'] ?? 0) === $departmentId
            );

            if ($department) {

                $this->candidate['department'] =
                    $department['name']
                    ?? $department['department']
                    ?? null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Programme
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | getPrograms() must receive the program_type_id.
        |
        */

        $programId =
            isset($this->candidate['program_id'])
                ? (int) $this->candidate['program_id']
                : null;

        if ($departmentId && $programId) {

            $programs = $client->getPrograms(
                $departmentId,
                $programTypeId
            );

            $program = collect($programs)->first(
                fn ($item) =>
                    (int) ($item['id'] ?? 0) === $programId
            );

            if ($program) {

                $this->candidate['program'] =
                    $program['name']
                    ?? $program['program']
                    ?? null;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Load Academic Sessions
    |--------------------------------------------------------------------------
    */

    protected function loadAcademicSessions(): void
{
    try {

        $client = app(AdmissionsPortalClient::class);

        $sessions = collect(
            $client->getAcadSessions()
        );

        /*
        |--------------------------------------------------------------------------
        | Current Session
        |--------------------------------------------------------------------------
        |
        | The current academic session is the one marked as visible.
        |
        */

        $currentSession = $sessions->first(
            fn ($session) =>
                (int) ($session['is_visible'] ?? 0) === 1
        );


        /*
        |--------------------------------------------------------------------------
        | Previous Session
        |--------------------------------------------------------------------------
        |
        | Sessions are expected to be returned in descending order
        | by academic session/year.
        |
        */

        $previousSession = null;

        if ($currentSession) {

            $currentIndex = $sessions->search(
                fn ($session) =>
                    (int) ($session['id'] ?? 0) ===
                    (int) ($currentSession['id'] ?? 0)
            );

            if ($currentIndex !== false) {

                $previousSession = $sessions
                    ->slice($currentIndex + 1)
                    ->first();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Dropdown Sessions
        |--------------------------------------------------------------------------
        */

        $this->academicSessions = collect([
            $currentSession,
            $previousSession,
        ])
            ->filter()
            ->unique('id')
            ->values()
            ->all();

    } catch (\Throwable $e) {

        Log::error(
            'Change Academic Session → sessions lookup failed',
            [
                'error' => $e->getMessage(),
            ]
        );

        $this->academicSessions = [];
    }
}


    /*
    |--------------------------------------------------------------------------
    | Confirm Change
    |--------------------------------------------------------------------------
    */

    public function confirmChange(): void
    {
        $this->resetError();

        $this->validate([
            'acadSessionId' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);


        if (!$this->candidate) {

            $this->errorMessage =
                'Please find a candidate first.';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Do Not Allow Same Session
        |--------------------------------------------------------------------------
        */

        if (
            $this->currentSessionId &&
            (int) $this->acadSessionId ===
            (int) $this->currentSessionId
        ) {

            $this->errorMessage =
                'The candidate is already registered under this academic session.';

            return;
        }


        Flux::modal(
            'change-academic-session-confirmation'
        )->show();
    }


    /*
    |--------------------------------------------------------------------------
    | Commit Change
    |--------------------------------------------------------------------------
    */

    public function changeAcademicSession(): void
    {
        $this->resetError();

        $this->validate([
            'acadSessionId' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);


        if (!$this->candidate) {

            $this->errorMessage =
                'Please find a candidate first.';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Do Not Allow Same Session
        |--------------------------------------------------------------------------
        */

        if (
            $this->currentSessionId &&
            (int) $this->acadSessionId ===
            (int) $this->currentSessionId
        ) {

            $this->errorMessage =
                'The candidate is already registered under this academic session.';

            return;
        }


        $candidateId =
            (int) ($this->candidate['id'] ?? 0);


        if ($candidateId < 1) {

            $this->errorMessage =
                'Invalid candidate.';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Capture Previous / New Values
        |--------------------------------------------------------------------------
        */

        $previousSession = [
            'acad_session_id' => $this->currentSessionId,
            'name'            => $this->currentSessionName,
        ];


        $newSessionId =
            (int) $this->acadSessionId;


        $newSessionName =
            $this->selectedSessionName();


        $newSession = [
            'acad_session_id' => $newSessionId,
            'name'            => $newSessionName,
        ];


        $this->processing = true;


        try {

            /*
            |--------------------------------------------------------------------------
            | Log Change
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Change Academic Session → Admissions Portal change',
                [
                    'candidate_id' => $candidateId,

                    'regno' =>
                        $this->candidate['regno']
                        ?? $this->regno,

                    'previous' =>
                        $previousSession,

                    'new' =>
                        $newSession,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Commit to Admissions Portal
            |--------------------------------------------------------------------------
            */

            $client = app(
                AdmissionsPortalClient::class
            );


            $result =
                $client->changeAcademicSession(
                    candidateId: $candidateId,
                    acadSessionId: $newSessionId,
                );


            /*
            |--------------------------------------------------------------------------
            | Verify API Success
            |--------------------------------------------------------------------------
            */

            if (($result['status'] ?? null) !== 'success') {

                $message =
                    $result['error']
                    ?? 'Unable to change academic session.';

                throw new \RuntimeException(
                    is_string($message)
                        ? $message
                        : json_encode($message)
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Use API Response As Authoritative Candidate
            |--------------------------------------------------------------------------
            |
            | The change-session endpoint returns the full CandidateResource,
            | including:
            |
            | program_type
            | acadSession
            | faculty
            | department
            | program
            |
            */

           /*
|--------------------------------------------------------------------------
| Update Candidate State
|--------------------------------------------------------------------------
|
| The change-session endpoint intentionally returns a minimal
| response. Preserve the existing candidate data, including
| resolved display names, and update only the changed session.
|
*/

$data = $result['data']['data'] ?? null;

if (!is_array($data)) {
    throw new \RuntimeException(
        'Invalid academic session change response.'
    );
}

$this->candidate['acad_session_id'] =
    (int) ($data['acad_session_id'] ?? $newSessionId);

/*
|--------------------------------------------------------------------------
| Keep Existing Resolved Names
|--------------------------------------------------------------------------
|
| The session name is already available from the session selected
| by the admission officer.
|
*/

$this->candidate['acadSession'] =
    $newSessionName;


/*
|--------------------------------------------------------------------------
| Update Component State
|--------------------------------------------------------------------------
*/

$this->currentSessionId =
    (int) $this->candidate['acad_session_id'];

$this->currentSessionName =
    $newSessionName;


            /*
            |--------------------------------------------------------------------------
            | Update Current Session
            |--------------------------------------------------------------------------
            */

            $this->currentSessionId =
                isset($this->candidate['acad_session_id'])
                    ? (int) $this->candidate['acad_session_id']
                    : $newSessionId;


            $this->currentSessionName =
                $this->candidate['acadSession']
                ?? $this->candidate['acad_session']
                ?? $newSessionName;


            /*
            |--------------------------------------------------------------------------
            | Clear Proposed Selection
            |--------------------------------------------------------------------------
            */

            $this->acadSessionId = null;


            /*
            |--------------------------------------------------------------------------
            | Close Confirmation Modal
            |--------------------------------------------------------------------------
            */

            Flux::modal(
                'change-academic-session-confirmation'
            )->close();


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            Flux::toast(
                'Academic session changed successfully.',
                variant: 'success',
                position: 'top-right',
                duration: 5000
            );

        } catch (\Throwable $e) {

            Log::error(
                'Change Academic Session → commit failed',
                [
                    'candidate_id' => $candidateId,

                    'jamb_no' =>
                        $this->candidate['jamb_no']
                        ?? $this->candidate['regno']
                        ?? $this->regno,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            $this->errorMessage =
                $e->getMessage();

        } finally {

            $this->processing = false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Selected Session Name
    |--------------------------------------------------------------------------
    */

    public function selectedSessionName(): ?string
    {
        if (!$this->acadSessionId) {
            return null;
        }

        $session = collect(
            $this->academicSessions
        )->first(
            fn ($item) =>
                (int) ($item['id'] ?? 0) ===
                (int) $this->acadSessionId
        );

        return $session['name']
            ?? $session['session_name']
            ?? null;
    }


    /*
    |--------------------------------------------------------------------------
    | Candidate Name
    |--------------------------------------------------------------------------
    */

    public function candidateName(): string
    {
        $user =
            $this->candidate['user']
            ?? [];


        return trim(
            implode(
                ' ',
                array_filter([
                    $user['last_name'] ?? null,
                    $user['first_name'] ?? null,
                    $user['other_names'] ?? null,
                ])
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Candidate
    |--------------------------------------------------------------------------
    */

    public function resetCandidate(): void
    {
        if ($this->processing) {
            return;
        }

        $this->clearCandidate();

        $this->regno = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Candidate
    |--------------------------------------------------------------------------
    */

    protected function clearCandidate(): void
    {
        $this->candidate = null;

        $this->currentSessionId = null;

        $this->currentSessionName = null;

        $this->acadSessionId = null;
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Error
    |--------------------------------------------------------------------------
    */

    protected function resetError(): void
    {
        $this->errorMessage = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.admissions.change-academic-session'
        );
    }
}
