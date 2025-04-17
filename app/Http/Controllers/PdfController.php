<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePdfJob;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    /**
     * Queue a job to generate a PDF based on simulated email correspondence
     *
     * @return \Illuminate\Http\Response
     */
    public function generate()
    {
        try {
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
}
