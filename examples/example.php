<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

require_once __DIR__ . '/myhtaccess.php';
require_once __DIR__ . '/base.class.php';

createHtAccessFromConfig([
    __DIR__ . '/config/core.yml',
    __DIR__ . '/config/api.yml'
], 'api', BaseHtAccess::class);

createHtAccessFromConfig([
    __DIR__ . '/config/core.yml',
    __DIR__ . '/config/web.yml',
    __DIR__ . '/config/front.yml',
], 'front', BaseHtAccess::class);

createHtAccessFromConfig([
    __DIR__ . '/config/core.yml',
    __DIR__ . '/config/web.yml',
    __DIR__ . '/config/front2.yml',
], 'front2', BaseHtAccess::class);

createHtAccessFromConfig([
    __DIR__ . '/config/core.yml',
    __DIR__ . '/config/web.yml',
    __DIR__ . '/config/front.yml',
    __DIR__ . '/config/dev.yml',
], 'dev', BaseHtAccess::class);
