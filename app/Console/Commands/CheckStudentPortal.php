<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckStudentPortal extends Command
{
    protected $signature = 'check:student-portal';
    protected $description = 'Check connectivity and auth to the Student Portal service';

    public function handle()
    {
        $url   = rtrim(config('services.student_portal.url'), '/').'/ping';
        $token = config('services.student_portal.token');

        $this->info("Checking Student Portal at: $url");

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                $this->info('✅ Connection successful:');
                $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            }

            $this->error("❌ Failed with HTTP {$response->status()}");
            $this->line($response->body());
            return Command::FAILURE;

        } catch (\Throwable $e) {
            $this->error('❌ Exception: '.$e->getMessage());
            Log::error('check:student-portal failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return Command::FAILURE;
        }
    }
}
