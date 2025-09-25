<?php

namespace App\Services\Clients;

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

}
