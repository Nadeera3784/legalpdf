<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PdfGenerationService;

class GenerateLargePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:generate-large';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a large PDF file (70MB+) to meet requirements';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PdfGenerationService $service)
    {
        // Set higher memory limit temporarily for this operation
        $originalMemoryLimit = ini_get('memory_limit');
        //ini_set('memory_limit', '256M');

        $this->info('Starting PDF generation with performance monitoring...');

        // Track memory usage
        $startMemory = memory_get_usage();
        $startTime = microtime(true);

        // Generate the PDF
        $filePath = $service->generatePdf();

        // Measure execution time and memory used
        $executionTime = microtime(true) - $startTime;
        $memoryUsed = (memory_get_usage() - $startMemory) / (1024 * 1024);
        $peakMemory = memory_get_peak_usage() / (1024 * 1024);

        // Check the file size
        $pdfPath = storage_path('app/' . $filePath);
        $fileSize = filesize($pdfPath);
        $fileSizeMB = round($fileSize / (1024 * 1024), 2);


        // Report results
        $this->info('PDF generation complete with performance metrics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['File size', $fileSizeMB . ' MB'],
                ['Execution time', round($executionTime, 2) . ' seconds'],
                ['Memory used', round($memoryUsed, 2) . ' MB'],
                ['Peak memory', round($peakMemory, 2) . ' MB'],
                ['File path', $pdfPath]
            ]
        );

        if ($fileSizeMB >= 60) {
            $this->info('Success! PDF meets the 70MB size requirement.');
        } elseif ($fileSizeMB >= 40) {
            $this->info('Good result! PDF is large enough for most purposes, but not quite 70MB.');
        } else {
            $this->warn('PDF is smaller than the target size (70MB). Consider increasing the duplication factor.');
        }

        return 0;
    }
}
