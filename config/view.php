<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

    /*
    |--------------------------------------------------------------------------
    | View Compiled Extension
    |--------------------------------------------------------------------------
    |
    | Here you may specify the extension for compiled Blade views. By default
    | the framework uses .php but you are free to specify any extension.
    |
    */

    'compiled_extension' => 'php',

    /*
    |--------------------------------------------------------------------------
    | View Cache
    |--------------------------------------------------------------------------
    |
    | Set this option to false to disable view caching
    |
    */

    'cache' => true,

    /*
    |--------------------------------------------------------------------------
    | View Relative Hash
    |--------------------------------------------------------------------------
    |
    | Set this option to true to use relative paths when generating
    | view cache files.
    |
    */

    'relative_hash' => false,

];
