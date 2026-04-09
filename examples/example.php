<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

require_once __DIR__ . '/myhtaccess.php';
require_once __DIR__ . '/base.class.php';

// Website example: full-featured frontend .htaccess
createHtAccessFromConfig([
    __DIR__ . '/config/core.yml',
    __DIR__ . '/config/web.yml',
    __DIR__ . '/config/front.yml',
    __DIR__ . '/config/website.yml',
], 'website', BaseHtAccess::class);

// API example: lightweight API .htaccess
createHtAccessFromConfig([
    __DIR__ . '/config/core.yml',
    __DIR__ . '/config/api.yml',
], 'api', BaseHtAccess::class);

// Dev example: website with basic auth protection
createHtAccessFromConfig([
    __DIR__ . '/config/core.yml',
    __DIR__ . '/config/web.yml',
    __DIR__ . '/config/front.yml',
    __DIR__ . '/config/website.yml',
    __DIR__ . '/config/dev.yml',
], 'dev', BaseHtAccess::class);
