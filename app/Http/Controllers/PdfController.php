<?php

namespace App\Http\Controllers;

use App\Services\PdfGenerationService;
use App\Jobs\GeneratePdfJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    protected $pdfService;

    public function __construct(PdfGenerationService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Queue a job to generate a PDF based on simulated email correspondence
     *
     * @return \Illuminate\Http\Response
     */
    public function generate()
    {
        try {
            // Dispatch job to generate the PDF in the background
            GeneratePdfJob::dispatch();

            return response()->json([
                'success' => true,
                'message' => 'PDF generation has been queued. It will run in the background.',
                'note' => 'Check the logs for PDF generation status and details.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue PDF generation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to queue PDF generation: ' . $e->getMessage()
            ], 500);
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

    /**
     * Display a dashboard for monitoring PDF generation
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        try {
            // List all generated PDFs
            $files = Storage::files('public');

            // Filter for PDFs
            $pdfFiles = array_filter($files, function ($file) {
                return strpos($file, 'email_thread_') !== false && strpos($file, '.pdf') !== false;
            });

            // Get file details
            $fileDetails = [];
            foreach ($pdfFiles as $file) {
                $fileSize = Storage::size($file);
                $lastModified = Storage::lastModified($file);

                $fileDetails[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->getHumanReadableSize($fileSize),
                    'size_bytes' => $fileSize,
                    'created_at' => date('Y-m-d H:i:s', $lastModified),
                    'url' => asset(Storage::url($file))
                ];
            }

            // Sort by last modified (newest first)
            usort($fileDetails, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            // Pass data to the view
            return view('pdf-dashboard', ['files' => $fileDetails]);
        } catch (\Exception $e) {
            Log::error('Failed to load dashboard: ' . $e->getMessage());
            return view('pdf-dashboard', ['error' => $e->getMessage()]);
        }
    }
}
