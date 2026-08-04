<?php

namespace App\Livewire\Jamb;

use App\Models\Admissions\JambImportBatch;
use App\Services\JambDataImport\ExcelAnalysisService;
use App\Services\JambDataImport\JambImportService;
use App\Services\JambDataImport\JambMappingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
//use App\Services\JambDataImport\JambImportService;

class ImportDashboard extends Component
{
    use WithFileUploads;

    public $file;

    public ?array $analysis = null;

    public array $manualMappings = [];

    public bool $analysisCompleted = false;

    public string $uploadedFilePath = '';

    public ?array $importResult = null;

    public string $originalFilename = '';

    public function analyze(
        ExcelAnalysisService $analysisService,
        JambMappingService $mappingService
    )
    {
         //dd($this->file);
        $this->validate([
            'file' => [
                'required',
                'file',
                'mimes:xls,xlsx',
                'max:51200'
            ]
        ]);

        $storedFile = $this->file->store(
            'jamb-imports'
        );

        $this->uploadedFilePath = storage_path(
            'app/private/' . $storedFile
        );

        $result = $analysisService->analyze(
            $this->uploadedFilePath
        );

        $mappingService->saveAutoMappings(
            $result->autoMatched
        );

        $this->analysis = $result->toArray();

        $this->analysisCompleted = true;

        $this->originalFilename =
    $this->file->getClientOriginalName();
    }



    public function saveMappings(
        JambMappingService $mappingService
    )
    {
        $payload = [];

        foreach ($this->manualMappings as $course => $programId) {

            if (!$programId) {
                continue;
            }

            $payload[] = [
                'course' => $course,
                'program_id' => $programId
            ];
        }

        $mappingService->saveManualMappings(
            $payload
        );

        session()->flash(
            'success',
            'Mappings saved successfully.'
        );
    }

 public function import(
    JambImportService $importService
)
{

    set_time_limit(0);

    ini_set(
        'memory_limit',
        '-1'
    );

    if (
        empty($this->uploadedFilePath)
    ) {

        session()->flash(
            'error',
            'No analyzed file found.'
        );

        return;
    }

    $batch = JambImportBatch::create([

        'filename' =>
            basename(
                $this->originalFilename,
            ),
        'admission_year' => date('Y'),
        'status' => 'pending',
        'uploaded_by' => Auth::id(),
        'uploaded_at' => now(),
    ]);

    $this->importResult =
        $importService->import(
            $this->uploadedFilePath,
            $batch->id
        );

    session()->flash(
        'success',
        'JAMB data imported successfully.'
    );
}



    public function render(
        JambMappingService $mappingService
    )
    {
        return view(
            'livewire.jamb.import-dashboard',
            [
                'programs' => $mappingService
                    ->mappingPrograms()
            ]
        );
    }
}
