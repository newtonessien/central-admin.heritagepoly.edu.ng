<?php

namespace App\Services\Clients;

use Illuminate\Support\Facades\Http;
use App\Services\Clients\Concerns\CallsRemoteApi;

class AdmissionsPortalClient
{
    use CallsRemoteApi;

    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
   $this->baseUrl = config('services.admissions.url') ?? '';
  $this->token   = config('services.admissions.token') ?? '';

    }

    public function getProgramTypes(): array
    {
        $resp = $this->http($this->token)
            ->get("{$this->baseUrl}/program-type");

        logger()->info('Admissions API response', [
        'status' => $resp->status(),
        'body'   => $resp->body(),
    ]);

        return $resp->json('data') ?? [];
    }

    public function getFaculties(): array
    {
        $resp = $this->http($this->token)
            ->get("{$this->baseUrl}/faculties");

        return $resp->json('data') ?? [];
    }
   public function getDepartments(int $facultyId): array
{
    $resp = $this->http($this->token)
        ->get("{$this->baseUrl}/departments", [
            'faculty_id' => $facultyId,
        ]);

    return $resp->json('data') ?? [];
}

public function getPrograms(int $departmentId, int $programTypeId): array
{
    $resp = $this->http($this->token)
        ->get("{$this->baseUrl}/programs", [
            'department_id'   => $departmentId,
            'program_type_id' => $programTypeId,
        ]);

    return $resp->json('data') ?? [];
}

// AdmissionsPortalClient.php
public function getApplicationTypes()
{
    return Http::withToken(config('services.admissions.token'))
        ->get($this->baseUrl.'/application-types');
}


    // AdmissionsPortalClient.php
public function changeApplicationType(string $regno, int $applicationTypeId)
{
    return Http::withToken(config('services.admissions.token'))
        ->patch($this->baseUrl.'/candidates/change-application-type', [
            'regno' => $regno,
            'application_type_id' => $applicationTypeId,
        ]);
}

}
