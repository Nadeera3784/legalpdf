<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Smalot\PdfParser\Parser;

class PdfGenerationService
{
    /**
     * Gmail addresses for simulation
     */
    protected $senderEmail = 'sender@gmail.com';
    protected $recipientEmail = 'recipient@gmail.com';

    /**
     * Number of email exchanges to simulate
     */
    protected $emailExchangeCount = 25; // From requirement: 25 email exchanges

    /**
     * Path to the content PDF file
     */
    protected $contentPdfPath = 'Content.pdf';

    /**
     * Duplication factor to increase content size
     * Higher number means larger PDF file
     */
    protected $duplicationFactor = 30000;

    /**
     * Control font size to increase pages
     */
    protected $fontSize = 50; // Increased from 30 to 50 for much larger output

    /**
     * Base64 image size in MB - higher increases file size dramatically
     */
    protected $imageSize = 10; // Size in MB per image

    /**
     * Number of base64 images per email
     */
    protected $imagesPerEmail = 3; // Include multiple images in each email

    /**
     * Custom log file path
     */
    protected $logFile = 'storage/logs/pdf_generation.log';

    /**
     * Custom logging method since Laravel's logging might not be working
     */
    protected function writeLog($message, $type = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
        file_put_contents(base_path($this->logFile), $logMessage, FILE_APPEND);
    }

    /**
     * Simulate email thread and generate PDF
     *
     * @return string File path of the generated PDF
     */
    public function generatePdf()
    {
        $this->writeLog("Starting PDF generation process with target size of 70MB");

        // Extract content from the provided PDF
        $emailContent = $this->extractContentFromPdf();

        // Simulate email thread
        $emailThread = $this->simulateEmailThread($emailContent);

        // Generate PDF from email thread
        $pdfPath = $this->generatePdfFromThread($emailThread);

        return $pdfPath;
    }

    /**
     * Extract content from the provided PDF
     *
     * @return string The extracted text content
     */
    protected function extractContentFromPdf()
    {
        try {
            // First try to extract content from Content.pdf
            if (Storage::exists($this->contentPdfPath)) {
                $pdfPath = Storage::path($this->contentPdfPath);
                $this->writeLog('Content.pdf file found at path: ' . $pdfPath);

                $parser = new Parser();
                $pdf = $parser->parseFile($pdfPath);
                $text = $pdf->getText();

                if (!empty($text)) {
                    // Duplicate the text many times to increase final size
                    $text = str_repeat($text, 50);
                    $this->writeLog('Content duplicated 50 times to increase size');

                    return $text;
                }

                $this->writeLog('Content.pdf exists but no text was extracted, falling back to generated content', 'WARNING');
            } else {
                $this->writeLog('Content.pdf file not found at path: ' . Storage::path('') . $this->contentPdfPath . ', falling back to generated content', 'WARNING');
            }

            return "";

        } catch (\Exception $e) {
            $this->writeLog('Content extraction failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), 'ERROR');

        }
    }

    /**
     * Simulate an email thread between two addresses
     *
     * @param string $emailContent Base content to use in emails
     * @return array The simulated email thread
     */
    protected function simulateEmailThread($emailContent)
    {
        $thread = [];
        $contentChunks = $this->splitContentIntoChunks($emailContent, $this->emailExchangeCount);

        // Create more participants for a more realistic thread
        $participants = [
            ['email' => $this->senderEmail, 'name' => 'John Doe', 'title' => 'Senior Legal Counsel'],
            ['email' => $this->recipientEmail, 'name' => 'Jane Smith', 'title' => 'Legal Director'],
            ['email' => 'michael.johnson@legaldept.com', 'name' => 'Michael Johnson', 'title' => 'Partner'],
            ['email' => 'sarah.williams@lawfirm.com', 'name' => 'Sarah Williams', 'title' => 'Associate'],
            ['email' => 'robert.brown@counsel.com', 'name' => 'Robert Brown', 'title' => 'General Counsel']
        ];

        $this->writeLog("Generating email thread with {$this->emailExchangeCount} exchanges");

        for ($i = 0; $i < $this->emailExchangeCount; $i++) {
            // Select participants for this email
            $senderIndex = $i % count($participants);
            $recipientIndex = ($i + 1) % count($participants);

            $sender = $participants[$senderIndex];
            $recipient = $participants[$recipientIndex];

            // Create email with timestamp (7 days ago + incrementing hours)
            $timestamp = time() - (7 * 24 * 60 * 60) + ($i * 60 * 60);
            $date = date('D, d M Y H:i:s O', $timestamp);

            // Create subject with incrementing number to show thread progress
            $subject = 'Re: Legal Document Review - Part ' . ($i + 1);
            if ($i === 0) {
                $subject = 'Legal Document Review - Initial Draft';
            } elseif ($i % 5 === 0) {
                // Add more variety to subjects
                $subjectVariations = [
                    'Updated Legal Document - Revision ' . ceil($i / 5),
                    'Follow-up on Legal Document Review - Iteration ' . ceil($i / 5),
                    'New Changes to Legal Agreement - Version ' . (1 + ceil($i / 5)),
                    'Important Updates to Contract - Please Review'
                ];
                $subject = $subjectVariations[array_rand($subjectVariations)];
            }

            // Add random attachment names to make emails look more realistic
            $attachments = [];
            if ($i % 2 === 0) { // Increased frequency of attachments from every 3rd to every 2nd email
                $attachmentTypes = [
                    'pdf' => ['Legal_Document', 'Contract', 'Agreement', 'Terms', 'NDA', 'Statement'],
                    'docx' => ['Contract_Revision', 'Draft_Agreement', 'Legal_Brief', 'Memorandum'],
                    'xlsx' => ['Financial_Terms', 'Cost_Analysis', 'Budget_Projection'],
                    'pptx' => ['Presentation', 'Legal_Overview', 'Case_Summary']
                ];

                // Add 2-4 attachments to increase file size
                $numAttachments = rand(2, 4);
                for ($j = 0; $j < $numAttachments; $j++) {
                    $type = array_rand($attachmentTypes);
                    $basename = $attachmentTypes[$type][array_rand($attachmentTypes[$type])];
                    $version = rand(1, 10);
                    $attachments[] = $basename . '_v' . $version . '.' . $type;
                }
            }

            // Create message headers with more metadata to increase file size
            $messageId = '<' . md5($sender['email'] . $timestamp) . '@gmail.com>';
            $inReplyTo = $i > 0 ? '<' . md5($participants[($i - 1) % count($participants)]['email'] . ($timestamp - 60 * 60)) . '@gmail.com>' : null;

            // Additional headers
            $headers = [
                'X-Mailer' => 'Microsoft Outlook 365',
                'X-Priority' => $i % 3 === 0 ? '1 (High)' : '3 (Normal)',
                'X-Spam-Status' => 'No, score=-2.0',
                'X-MS-Exchange-Organization-SCL' => '-1',
                'X-MS-Has-Attach' => !empty($attachments) ? 'yes' : 'no',
                'X-MS-Exchange-Organization-AuthAs' => 'Internal',
                'X-MS-Exchange-Transport-EndToEndLatency' => rand(100, 999) . ' msec',
                'X-Proofpoint-Virus-Version' => 'vendor=fsecure engine=2.50.10434:6.0.345,1.0.14,0.0.0000 definitions=2020-03-23_10:2020-03-23,2020-03-23,1970-01-01 signatures=0'
            ];

            // Add more content to each email
            $enhancedContent = $contentChunks[$i];

            // Add quoted text from previous emails to make it more realistic and larger
            if ($i > 0) {
                $quotedEmails = '';
                for ($q = $i - 1; $q >= max(0, $i - 3); $q--) {
                    $prevSender = $participants[$q % count($participants)];
                    $prevDate = date('D, d M Y H:i:s O', $timestamp - (($i - $q) * 60 * 60));

                    $quotedEmails .= "\n\n--- Original Message ---\n";
                    $quotedEmails .= "From: {$prevSender['name']} <{$prevSender['email']}>\n";
                    $quotedEmails .= "Date: {$prevDate}\n";
                    $quotedEmails .= "Subject: " . ($q === 0 ? 'Legal Document Review - Initial Draft' : 'Re: Legal Document Review - Part ' . ($q + 1)) . "\n\n";

                    // Add shortened version of previous content
                    $prevContent = substr($contentChunks[$q], 0, 1000);
                    $quotedEmails .= "> " . str_replace("\n", "\n> ", $prevContent) . "...\n";
                }

                $enhancedContent .= $quotedEmails;
            }

            // Add email to thread
            $thread[] = [
                'sender' => $sender['email'],
                'sender_name' => $sender['name'],
                'sender_title' => $sender['title'],
                'recipient' => $recipient['email'],
                'recipient_name' => $recipient['name'],
                'cc' => $i % 3 === 0 ? [
                    $participants[($i + 2) % count($participants)]['email'],
                    $participants[($i + 3) % count($participants)]['email']
                ] : [],
                'subject' => $subject,
                'date' => $date,
                'content' => $enhancedContent,
                'message_id' => $messageId,
                'in_reply_to' => $inReplyTo,
                'attachments' => $attachments,
                'headers' => $headers
            ];
        }

        return $thread;
    }

    /**
     * Split content into roughly equal chunks
     *
     * @param string $content
     * @param int $numChunks
     * @return array
     */
    protected function splitContentIntoChunks($content, $numChunks)
    {
        // Instead of equal divisions, create chunks of varying sizes for better efficiency
        $chunks = [];
        $baseSize = floor(strlen($content) / $numChunks);

        // Make sure we have enough content for all chunks
        $contentLength = strlen($content);
        if ($baseSize * $numChunks > $contentLength) {
            // Duplicate content to ensure we have enough for all chunks
            $repeats = ceil(($baseSize * $numChunks) / $contentLength);
            $content = str_repeat($content, $repeats);
            $this->writeLog("Content duplicated {$repeats} times to ensure enough for {$numChunks} chunks");
        }

        for ($i = 0; $i < $numChunks; $i++) {
            // Vary chunk size slightly to add randomness
            $variableSize = $baseSize + rand(-5000, 5000);
            $variableSize = max(1000, $variableSize); // Ensure minimum size

            // Get a chunk of content, or reuse from beginning if we run out
            $startPos = ($i * $baseSize) % strlen($content);
            $chunk = substr($content, $startPos, $variableSize);

            // Add email-like formatting with extensive content
            $formattedContent = "Hi,\n\nPlease find my comments on the document below:\n\n" . $chunk . "\n\n";

            // Add legal signature with more details to increase content size
            $signatures = [
                "Best regards,\nJohn Doe\nSenior Legal Counsel\nLegal Department\nPhone: (555) 123-4567\nEmail: john.doe@legalfirm.com\n\nCONFIDENTIALITY NOTICE: This email and any attachments are confidential and may be protected by legal privilege. If you are not the intended recipient, be aware that any disclosure, copying, distribution, or use of this email or any attachment is prohibited. If you have received this email in error, please notify us immediately by returning it to the sender and delete this copy from your system.",
                "Yours sincerely,\nJane Smith\nLegal Director\nCorporate Legal Affairs\nPhone: (555) 987-6543\nEmail: jane.smith@legalfirm.com\n\nDISCLAIMER: The information contained in this email message is intended only for the personal and confidential use of the recipient(s) named above. This message may be an attorney-client communication and/or work product and as such is privileged and confidential.",
                "Regards,\nMichael Johnson\nPartner\nLitigation Department\nPhone: (555) 234-5678\nEmail: m.johnson@legalfirm.com\n\nPRIVILEGED AND CONFIDENTIAL: This electronic message contains information that is confidential and may be protected by the attorney-client privilege and/or work product doctrine."
            ];

            $formattedContent .= $signatures[$i % count($signatures)];

            $chunks[] = $formattedContent;
        }

        return $chunks;
    }

    /**
     * Create HTML representation of the email thread
     * Optimized for performance
     *
     * @param array $emailThread
     * @return string HTML content
     */
    protected function createHtmlFromThread($emailThread)
    {
        // Predefine template parts for better performance through string concatenation
        $htmlHeader = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Thread</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background-color: #f9f9f9;
        }
        .email { 
            margin-bottom: 30px; 
            border: 1px solid #ddd; 
            padding: 15px; 
            page-break-inside: avoid;
        }
        .email-header { 
            background-color: #f2f2f2; 
            padding: 15px; 
            margin-bottom: 15px;
        }
        .email-metadata { 
            color: #555; 
            font-size: 14px; 
            margin-bottom: 10px;
        }
        .email-subject { 
            font-weight: bold; 
            margin-bottom: 10px;
            font-size: 18px;
        }
        .email-content { 
            white-space: pre-wrap; 
            font-size: ' . $this->fontSize . 'px;
            padding: 10px;
        }
        .sender { 
            font-weight: bold;
        }
        .attachment { 
            margin-top: 20px; 
            border: 1px dashed #ccc; 
            padding: 15px; 
            background-color: #f5f5f5;
        }
        .attachment-icon { 
            display: inline-block; 
            width: 20px; 
            height: 20px; 
            background-color: #ddd; 
            margin-right: 8px;
        }
        .signature { 
            margin-top: 20px; 
            border-top: 1px solid #eee; 
            padding-top: 10px;
        }
        h1 {
            font-size: 32px;
            text-align: center;
        }
        .large-image {
            width: 100%;
            max-width: 100%;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Email Correspondence Documentation</h1>
    <div style="padding: 20px; background-color: #ecf0f1; margin-bottom: 20px;">
        <p style="margin: 0; font-size: 16px;">This document contains a complete record of email correspondence between parties. Total of ' . $this->emailExchangeCount . ' email exchanges are documented in this PDF file. Generated for testing purposes with a target size of 70MB.</p>
    </div>';

        $htmlFooter = '
</body>
</html>';

        // Generate large base64 placeholders for images - more images for larger file size
        $this->writeLog("Generating base64 images for HTML content");
        $totalImages = $this->emailExchangeCount * $this->imagesPerEmail;
        $imageBlocks = $this->generateLargeBase64Images($totalImages);
        $this->writeLog("Generated " . count($imageBlocks) . " base64 images for HTML content");

        // Add emails in reverse chronological order (newest first)
        $emailThread = array_reverse($emailThread);

        // Build emails in chunks
        $htmlChunks = [];
        $htmlChunks[] = $htmlHeader;

        foreach ($emailThread as $index => $email) {
            $emailHtml = '
    <div class="email">
        <div class="email-header">
            <div class="email-subject">' . htmlspecialchars($email['subject']) . '</div>
            <div class="email-metadata">
                <span class="sender">From:</span> ' . htmlspecialchars($email['sender_name']) . ' &lt;' . htmlspecialchars($email['sender']) . '&gt; (' . htmlspecialchars($email['sender_title']) . ')<br>
                <span class="sender">To:</span> ' . htmlspecialchars($email['recipient_name']) . ' &lt;' . htmlspecialchars($email['recipient']) . '&gt;<br>';

            // Add CC recipients if any
            if (!empty($email['cc'])) {
                $emailHtml .= '                <span class="sender">CC:</span> ' . htmlspecialchars(implode(', ', $email['cc'])) . '<br>';
            }

            $emailHtml .= '                <span class="sender">Date:</span> ' . htmlspecialchars($email['date']) . '<br>
                <span class="sender">Message-ID:</span> ' . htmlspecialchars($email['message_id']) . '<br>';

            if ($email['in_reply_to']) {
                $emailHtml .= '                <span class="sender">In-Reply-To:</span> ' . htmlspecialchars($email['in_reply_to']) . '<br>';
            }

            // Display additional headers
            if (!empty($email['headers'])) {
                $emailHtml .= '                <div class="additional-headers">';
                foreach ($email['headers'] as $header => $value) {
                    $emailHtml .= '                    <span class="sender">' . htmlspecialchars($header) . ':</span> ' . htmlspecialchars($value) . '<br>';
                }
                $emailHtml .= '                </div>';
            }

            $emailHtml .= '            </div>
        </div>
        <div class="email-content">' . nl2br(htmlspecialchars($email['content'])) . '</div>';

            // Add multiple large image placeholders to each email
            for ($i = 0; $i < $this->imagesPerEmail; $i++) {
                if (count($imageBlocks) > 0) {
                    $imageIndex = ($index * $this->imagesPerEmail + $i) % count($imageBlocks);
                    $imageBlock = $imageBlocks[$imageIndex];
                    $emailHtml .= '<div style="margin: 20px 0;"><img src="' . $imageBlock . '" class="large-image"></div>';
                }
            }

            // Add attachments if any
            if (!empty($email['attachments'])) {
                $emailHtml .= '<div class="attachment">';
                $emailHtml .= '<div style="font-weight: bold; margin-bottom: 10px;">Attachments (' . count($email['attachments']) . '):</div>';
                foreach ($email['attachments'] as $attachment) {
                    $fileExt = pathinfo($attachment, PATHINFO_EXTENSION);
                    $fileIcon = $this->getFileIcon($fileExt);
                    $emailHtml .= '<div><span class="attachment-icon" style="background-color: ' . $fileIcon . ';"></span> ' . htmlspecialchars($attachment) . ' (' . rand(50, 5000) . 'KB)</div>';
                }
                $emailHtml .= '</div>';
            }

            $emailHtml .= '
    </div>';

            $htmlChunks[] = $emailHtml;
        }

        $htmlChunks[] = $htmlFooter;

        // Combine chunks efficiently
        $html = implode('', $htmlChunks);

        // Clean up to free memory
        unset($imageBlocks);
        unset($htmlChunks);

        return $html;
    }

    /**
     * Generate a PDF from the email thread
     *
     * @param array $emailThread
     * @return string The file path to the generated PDF
     */
    protected function generatePdfFromThread($emailThread)
    {
        // Release memory before starting PDF generation
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        // Increase memory limit if possible
        $this->tryIncreaseMemoryLimit();

        // Create HTML representation of the email thread
        $html = $this->createHtmlFromThread($emailThread);

        // Log the size of HTML content before PDF generation
        $htmlSize = strlen($html) / (1024 * 1024); // Size in MB
        $this->writeLog("HTML content size before PDF generation: {$htmlSize}MB");

        // Generate a filename with timestamp
        $filename = 'email_thread_' . date('Y-m-d_H-i-s') . '.pdf';

        // Configure PDF generation with optimized settings for performance while maintaining size
        $pdf = SnappyPdf::loadHTML($html);

        // Configure for extreme file size
        $pdf->setOption('page-size', 'A4');
        $pdf->setOption('margin-top', '10mm');
        $pdf->setOption('margin-right', '10mm');
        $pdf->setOption('margin-bottom', '10mm');
        $pdf->setOption('margin-left', '10mm');
        $pdf->setOption('dpi', 600); // Increased from 300 to 600 for larger file size
        $pdf->setOption('image-quality', 100); // Maximum quality for larger file
        $pdf->setOption('encoding', 'UTF-8');

        // Optimize for file size - we need a very large file (70MB)
        $pdf->setOption('disable-smart-shrinking', true);
        $pdf->setOption('lowquality', false);
        $pdf->setOption('disable-javascript', true);
        $pdf->setOption('no-pdf-compression', true); // Critical for large file size

        // Additional options to increase file size
        $pdf->setOption('image-dpi', 600); // Increased from 300 to 600
        $pdf->setOption('enable-smart-shrinking', false);
        $pdf->setOption('zoom', 2.0); // Increased from 1.5 to 2.0

        // Set a long timeout (60 minutes)
        $pdf->setTimeout(3600);

        $this->writeLog("Starting PDF generation with wkhtmltopdf");

        // Save PDF to storage
        $pdfOutput = $pdf->output();

        // Log the size of the generated PDF
        $pdfSize = strlen($pdfOutput) / (1024 * 1024); // Size in MB
        $this->writeLog("Generated PDF size: {$pdfSize}MB");

        // Create a file with binary data that's exactly 70MB in size
        $targetSizeMB = 70;
        $targetSizeBytes = $targetSizeMB * 1024 * 1024;

        // If we need to artificially increase the file size
        if (strlen($pdfOutput) < $targetSizeBytes) {
            $this->writeLog("PDF size {$pdfSize}MB is below target of 70MB, using direct file size manipulation");

            // Save the PDF first
            Storage::put('public/' . $filename, $pdfOutput);

            // Create a temp file with exactly 70MB of data
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_padding');
            $handle = fopen($tempFile, 'w');

            // Write the PDF data
            fwrite($handle, $pdfOutput);

            // Pad with zeros until we reach 70MB
            $paddingBytes = $targetSizeBytes - strlen($pdfOutput);
            $this->writeLog("Adding {$paddingBytes} bytes of padding to reach 70MB");

            // Write in chunks to avoid memory issues
            $chunkSize = 1024 * 1024; // 1MB chunks
            $remainingBytes = $paddingBytes;

            while ($remainingBytes > 0) {
                $writeSize = min($chunkSize, $remainingBytes);
                $padding = str_repeat("\0", $writeSize);  // Null bytes for padding
                fwrite($handle, $padding);
                $remainingBytes -= $writeSize;
            }

            fclose($handle);

            // Copy to storage
            $finalContent = file_get_contents($tempFile);
            Storage::put('public/' . $filename, $finalContent);

            // Clean up
            unlink($tempFile);

            $this->writeLog("Final PDF size: 70MB (with padding)");
        } else {
            Storage::put('public/' . $filename, $pdfOutput);
        }

        // Free memory
        unset($pdfOutput);
        unset($pdf);
        unset($html);

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        return 'public/' . $filename;
    }

    /**
     * Try to increase memory limit to accommodate large PDF generation
     */
    private function tryIncreaseMemoryLimit()
    {
        $currentLimit = ini_get('memory_limit');
        $currentLimitBytes = $this->getMemoryLimitInBytes($currentLimit);

        // Try to increase to 2GB if current limit is lower
        $targetLimit = 2 * 1024 * 1024 * 1024; // 2GB

        if ($currentLimitBytes < $targetLimit) {
            @ini_set('memory_limit', '2G');
        }
    }

    /**
     * Convert memory limit string to bytes
     *
     * @param string $memoryLimit
     * @return int
     */
    private function getMemoryLimitInBytes($memoryLimit)
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);

        switch ($last) {
            case 'g':
                $memoryLimit = (int)$memoryLimit * 1024 * 1024 * 1024;
                break;
            case 'm':
                $memoryLimit = (int)$memoryLimit * 1024 * 1024;
                break;
            case 'k':
                $memoryLimit = (int)$memoryLimit * 1024;
                break;
        }

        return (int)$memoryLimit;
    }

    /**
     * Get a background color for file type icon
     *
     * @param string $extension
     * @return string
     */
    private function getFileIcon($extension)
    {
        $colorMap = [
            'pdf' => '#FF0000',   // Red
            'docx' => '#0000FF',  // Blue
            'doc' => '#0000FF',   // Blue
            'xlsx' => '#008000',  // Green
            'xls' => '#008000',   // Green
            'pptx' => '#FFA500',  // Orange
            'ppt' => '#FFA500',   // Orange
            'txt' => '#808080',   // Gray
            'zip' => '#800080',   // Purple
            'jpg' => '#FF00FF',   // Magenta
            'png' => '#00FFFF',   // Cyan
        ];

        return $colorMap[$extension] ?? '#333333'; // Default dark gray
    }


    /**
     * Generate large base64 "images" (just encoded data that looks like images)
     * This creates large blocks of data with minimal processing
     *
     * @param int $count Number of image blocks to generate
     * @return array Array of base64 blocks
     */
    protected function generateLargeBase64Images($count = 5)
    {
        $images = [];

        // Base64 header to make it look like an image
        $imageHeader = 'data:image/jpeg;base64,';

        $this->writeLog("Generating {$count} large base64 images of approximately {$this->imageSize}MB each");

        for ($i = 0; $i < $count; $i++) {
            // Generate a massive base64 string (each MB is roughly 1,048,576 bytes)
            // We multiply by 0.75 because base64 encoding increases size by ~33%
            $size = round($this->imageSize * 1024 * 1024 * 0.75);
            $this->writeLog("Generating base64 image #{$i} with size {$size} bytes");
            $images[] = $imageHeader . $this->generatePseudoBase64($size);
        }

        return $images;
    }

    /**
     * Generate pseudo-base64 content to increase file size
     * Optimized for memory and speed
     *
     * @param int $length
     * @return string
     */
    protected function generatePseudoBase64($length = 10000)
    {
        // Using a simpler approach with repeated patterns for speed
        $pattern = str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/', 1000);
        $patternLength = strlen($pattern);

        // Calculate how many full patterns we need
        $fullPatterns = floor($length / $patternLength);
        $remainder = $length % $patternLength;

        // Build the string using str_repeat for full patterns (very fast)
        $result = str_repeat($pattern, $fullPatterns);

        // Add the remainder if needed
        if ($remainder > 0) {
            $result .= substr($pattern, 0, $remainder);
        }

        return $result;
    }
}
