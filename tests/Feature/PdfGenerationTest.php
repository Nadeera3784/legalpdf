<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\TestCase;
use App\Services\PdfGenerationService;
use Illuminate\Support\Facades\Queue;
use App\Jobs\GeneratePdfJob;

class PdfGenerationTest extends TestCase
{
    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock Content.pdf file if it doesn't exist
        if (!Storage::exists('Content.pdf')) {
            Storage::put('Content.pdf', 'Sample content for testing purposes.');
        }
    }

    /**
     * Test that the PDF generation endpoint works correctly.
     *
     * @return void
     */
    public function test_pdf_generation_endpoint()
    {
        // Fake the queue so the job doesn't actually run
        Queue::fake();

        // Make request to the endpoint
        $response = $this->getJson('/generate-pdf');

        // Assert the response is successful
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'note'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'PDF generation has been queued. It will run in the background.'
                ]);

        // Verify that the specific job was pushed to the queue
        Queue::assertPushed(GeneratePdfJob::class);
    }

    /**
     * Test the PDF generation service directly to verify file size.
     *
     * @return void
     */
    public function test_pdf_file_size()
    {
        // Get the service from the container
        $pdfService = app(PdfGenerationService::class);

        // Generate a PDF directly through the service
        $pdfPath = $pdfService->generatePdf();

        // Make sure the file exists
        $this->assertTrue(Storage::exists($pdfPath), 'Generated PDF file does not exist');

        // Get file size
        $fileSize = Storage::size($pdfPath);
        $fileSizeMB = round($fileSize / (1024 * 1024), 2);

        // Output file size for inspection
        echo "Generated PDF size: $fileSizeMB MB\n";

        // Check that the file is not empty
        $this->assertGreaterThan(0, $fileSize, 'PDF file is empty');

        // Minimum size check - at least 1MB to ensure it has some content
        $this->assertGreaterThan(1 * 1024 * 1024, $fileSize, "PDF file size is too small ($fileSizeMB MB)");
    }

    /**
     * Test that the generated PDF has the required email thread structure
     *
     * @return void
     */
    public function test_pdf_thread_structure()
    {
        // Get the service from the container
        $pdfService = app(PdfGenerationService::class);

        // Generate a PDF directly
        $pdfPath = $pdfService->generatePdf();

        // Make sure the file exists
        $this->assertTrue(Storage::exists($pdfPath), 'Generated PDF file does not exist');

        // Get the full path
        $fullPdfPath = storage_path('app/' . $pdfPath);

        // Parse the PDF
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($fullPdfPath);

            // Get all pages
            $pages = $pdf->getPages();
            $this->assertGreaterThan(0, count($pages), 'PDF does not have any pages');

            // Get the full text
            $text = $pdf->getText();
            $this->assertNotEmpty($text, 'PDF does not contain any text');

            // Basic structure checks
            if (strlen($text) > 100) {
                // Test for common content that should be in all PDFs
                $hasCorrespondence = strpos($text, 'Correspondence') !== false;
                $hasEmail = strpos($text, 'email') !== false || strpos($text, 'Email') !== false;

                // At least one of these should be true for a valid PDF
                $this->assertTrue($hasCorrespondence || $hasEmail, 'PDF does not contain expected content');
            }

        } catch (\Exception $e) {
            $this->fail('Failed to parse PDF: ' . $e->getMessage());
        }
    }

    /**
     * Test that the generated PDF contains exactly 94 email exchanges
     *
     * @return void
     */
    public function test_pdf_contains_94_email_threads()
    {
        // Get the service from the container
        $pdfService = app(PdfGenerationService::class);

        // Generate a PDF directly
        $pdfPath = $pdfService->generatePdf();

        // Make sure the file exists
        $this->assertTrue(Storage::exists($pdfPath), 'Generated PDF file does not exist');

        // Get the full path
        $fullPdfPath = storage_path('app/' . $pdfPath);

        // Parse the PDF
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($fullPdfPath);

            // Get all text from the PDF
            $text = $pdf->getText();

            // Count the number of email exchanges using "From:" as a marker
            $fromCount = substr_count($text, 'From:');

            // Log the count for debugging
            echo "Found $fromCount email exchanges in the PDF\n";

            // Assert that there are exactly 94 email exchanges
            $this->assertEquals(94, $fromCount, 'PDF should contain exactly 94 email exchanges');

            // Additional check: look for subject lines
            $subjectCount = substr_count($text, 'Subject:') + substr_count($text, 'Re:');
            $this->assertGreaterThanOrEqual(94, $subjectCount, 'Each email should have a subject line');

            // Verify the presence of both sender and recipient in the thread
            $this->assertStringContainsString('John Doe', $text, 'Sender name missing from email thread');
            $this->assertStringContainsString('Jane Smith', $text, 'Recipient name missing from email thread');

        } catch (\Exception $e) {
            $this->fail('Failed to parse PDF: ' . $e->getMessage());
        }
    }
}
