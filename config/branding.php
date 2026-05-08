<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Branding assets
    |--------------------------------------------------------------------------
    |
    | Both values can be either:
    |   - a relative public path (e.g. "images/logo.png" → resolves to
    |     public/images/logo.png and is served via asset()),
    |   - or a fully-qualified URL (e.g. "https://cdn.example.com/logo.svg")
    |     which is used as-is.
    |
    | Leave empty to use the built-in fallback (auto-detected files in
    | public/images/logo.{png,svg,jpg} or public/favicon.ico).
    |
    */

    'logo'    => env('APP_LOGO'),
    'favicon' => env('APP_FAVICON'),

];
