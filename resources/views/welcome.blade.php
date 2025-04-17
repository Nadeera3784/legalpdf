<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LegalPDF Generator</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f6f8;
        }
        .container {
            width: 100%;
            max-width: 800px;
            padding: 2rem;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }
        .card {
            border: 1px solid #ddd;
            padding: 1.5rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            background-color: #4a69bd;
            color: #fff;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #3c5aa6;
        }
        .info {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-left: 4px solid #4a69bd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LegalPDF Generator</h1>
        
        <div class="card">
            <h2>Generate PDF from Email Thread</h2>
            <p>Click the button below to simulate an email thread between two Gmail addresses and generate a PDF document from that thread.</p>
            <a href="/generate-pdf" class="btn">Generate PDF</a>
        </div>
        
        <div class="info">
            <h3>About This Application</h3>
            <p>This application simulates a Gmail correspondence between two email addresses. It uses content from a 3.5MB PDF file to generate email bodies, creating a back-and-forth email chain 25 times. The resulting PDF will be approximately 70MB in size.</p>
            <p><strong>Note:</strong> PDF generation may take a few moments to complete due to the size of the document.</p>
        </div>

        <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-1">
                <div class="p-6">
                    <div class="flex items-center">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <div class="ml-4 text-lg leading-7 font-semibold">
                            <a href="{{ url('/dashboard') }}" class="underline text-gray-900 dark:text-white">PDF Generation Dashboard</a>
                        </div>
                    </div>

                    <div class="ml-12">
                        <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                            View and manage your generated PDF files. The dashboard allows you to generate new PDFs and view/download existing ones.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 