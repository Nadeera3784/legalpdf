<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |
    |    Whether to enable snappy PDF generation.
    |
    | Binary:
    |
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timeout:
    |
    |    The amount of time to wait (in seconds) before the process times out.
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
    |
    */

    'pdf' => [
        'enabled' => true,
        'binary'  => '/usr/bin/wkhtmltopdf',
        'timeout' => 600,
        'options' => [
            'dpi' => 300,
            'enable-local-file-access' => true,
            'enable-javascript' => true,
            'javascript-delay' => 2000,
            'no-stop-slow-scripts' => true,
            'lowquality' => false,
            'print-media-type' => true,
            'encoding' => 'UTF-8',
            'enable-smart-shrinking' => true,
            'image-quality' => 100,
            'outline-depth' => 3,
        ],
        'env'     => [],
    ],

    'image' => [
        'enabled' => true,
        'binary'  => '/usr/bin/wkhtmltoimage',
        'timeout' => 120,
        'options' => [],
        'env'     => [],
    ],

];
