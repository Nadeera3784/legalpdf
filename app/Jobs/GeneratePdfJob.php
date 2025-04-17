<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PdfGenerationService;
use Illuminate\Support\Facades\Log;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour timeout

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     *
     * @param PdfGenerationService $pdfService
     * @return void
     */
    public function handle(PdfGenerationService $pdfService)
    {
        try {
            $startTime = microtime(true);

            // Generate the PDF
            $filePath = $pdfService->generatePdf();

            $executionTime = microtime(true) - $startTime;
            $fileSize = $this->getHumanReadableSize(filesize(storage_path('app/' . $filePath)));

            // Log successful generation
            Log::info('PDF generated successfully', [
                'file_path' => $filePath,
                'execution_time' => $executionTime . ' seconds',
                'file_size' => $fileSize
            ]);
        } catch (\Exception $e) {
            Log::error('PDF generation job failed: ' . $e->getMessage());

            // Optionally, you could retry the job
            // $this->release(60); // Release back to queue after 60 seconds
            throw $e;
        }
    }

    /**
     * Convert bytes to human-readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function getHumanReadableSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
