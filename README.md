# LegalPDF

This Laravel application simulates the generation of a large PDF file (~70MB) from a simulated Gmail correspondence between two email addresses.

## Requirements

- Docker and Docker Compose
- Approximately 100MB of disk space for the generated PDF

## Project Structure

- This is a Laravel 9 application with a backend-only API
- Uses wkhtmltopdf via Laravel Snappy for PDF generation
- Simulates email thread with 25 messages using the content from the provided Content.pdf

## Key Components

1. **PdfGenerationService** (`app/Services/PdfGenerationService.php`): Core service that handles:
   - Extracting text from the Content.pdf file
   - Simulating the email thread between two Gmail addresses
   - Splitting content into appropriate chunks
   - Generating HTML representation of the email chain
   - Producing the final large PDF file

2. **PdfController** (`app/Http/Controllers/PdfController.php`): Controller that:
   - Exposes the `/generate-pdf` endpoint
   - Handles PDF generation requests
   - Returns JSON response with metadata about the generated PDF

3. **Docker Setup**:
   - PHP 8.1 container with all necessary extensions
   - Nginx web server for serving the application
   - Configured for optimal PDF generation performance

## Installation and Setup

1. Clone this repository:
```
git clone <repository-url>
cd legalpdf
```

2. Start the Docker containers:
```
docker-compose up -d
```

3. Install dependencies:
```
docker-compose exec app composer install
```

4. Create storage directory symlink:
```
docker-compose exec app php artisan storage:link
```

5. Set proper permissions:
```
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache
```

## Generating the PDF

To generate the PDF, make an HTTP request to the `/generate-pdf` endpoint:

```
curl http://localhost:8000/generate-pdf
```

Or simply visit `http://localhost:8000/generate-pdf` in your browser.

The generated PDF will be saved to `storage/app/public/` with a timestamped filename like `email_thread_2023-04-26_12-34-56.pdf`.

## Monitoring Queue Jobs with Horizon

This application uses Laravel's queue system to process PDF generation in the background. To monitor these jobs:

1. Access the Laravel Horizon dashboard:
```
http://localhost:8000/horizon
```

2. From the Horizon dashboard, you can:
   - Monitor active, pending, and completed jobs
   - View job failure details
   - Check queue throughput and performance metrics
   - Restart failed jobs
   - See real-time queue processing status

3. To start Horizon (if not already running):
```
docker-compose exec app php artisan horizon
```

4. To pause and continue Horizon:
```
docker-compose exec app php artisan horizon:pause
docker-compose exec app php artisan horizon:continue
```

5. To check queue status from the command line:
```
docker-compose exec app php artisan queue:status
```

The PDF generation job may take several minutes to complete depending on system resources, and you can track its progress through the Horizon dashboard.

## Technical Details

- The application extracts text content from the provided Content.pdf
- It simulates a 25-message email thread between two Gmail addresses
- The content is split into chunks and formatted as email messages
- Each email contains headers, metadata, and message content
- The thread is formatted as HTML and converted to PDF
- The resulting PDF will be approximately 70MB in size


## Performance Considerations

- The PDF generation process is memory intensive due to the large size of the output
- The Docker environment is configured with appropriate memory limits
- The application is designed to handle large content volumes efficiently
- Progress monitoring is implemented to track the generation process

## Laravel Version

- Laravel 9.x

## Package Dependencies

### PDF Generation Packages

- **barryvdh/laravel-snappy**: Laravel wrapper for wkhtmltopdf/wkhtmltoimage - used for converting HTML to PDF with advanced styling and formatting support. This package is the core PDF generator that:
  - Handles complex HTML layouts including headers, footers and page numbers
  - Supports CSS styling for professional document appearance
  - Manages memory efficiently for large PDF generation

- **smalot/pdfparser**: PHP library to parse PDF files and extract data. Used in this application to:
  - Extract text from the source Content.pdf file
  - Parse PDF structure for content manipulation
  - Analyze PDF documents during testing to verify content integrity

### Queue Management

- **laravel/horizon**: Dashboard and code-driven configuration for Laravel Redis queues. Used to:
  - Monitor queue health and throughput
  - Provide real-time queue metrics
  - Manage failed jobs and retries
  - Track PDF generation progress

## Troubleshooting

### Session Configuration Issues

If you encounter an error related to the Cookie Service Provider, such as:

```
ErrorException: Trying to access array offset on value of type null in file 
/var/www/vendor/laravel/framework/src/Illuminate/Cookie/CookieServiceProvider.php
```

This is typically caused by incorrect session configuration. To resolve:

1. Check your `.env` file to ensure the `SESSION_DRIVER` is set to `file` (not `cookie`)
2. Verify the sessions directory exists with proper permissions:
   ```
   docker-compose exec app mkdir -p /var/www/storage/framework/sessions
   docker-compose exec app chmod -R 777 /var/www/storage/framework/sessions
   ```

## Testing

The application includes a comprehensive test suite to verify PDF generation functionality:

```
docker-compose exec app php artisan test
```

Tests include:
- Verification of the `/generate-pdf` endpoint
- Checking PDF file size (should be ~70MB)
- Validating the email thread structure
- Confirming the PDF contains exactly 94 email exchanges

Each test verifies a different aspect of the PDF generation process, ensuring the application produces the expected output consistently. 