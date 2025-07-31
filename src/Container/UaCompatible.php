<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Container\FilesMatch;
use JDZ\HtaccessMaker\Directive\Header;

/**
 * UaCompatible
 * 
 * Handles X-UA-Compatible header for browser compatibility.
 * It allows you to specify browser compatibility settings
 * and optionally unset the header for static files.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class UaCompatible extends Container
{
    protected array $defaults = [
        'browsers' => 'IE=Edge',
        'unsetOnStaticFiles' => true,
        'staticFilesExtensions' => [
            'js',
            'css',
            'gif',
            'png',
            'jpe?g',
            'pdf',
            'svg',
            'eot',
            'ttf',
            'otf',
            'woff',
            'woff2',
            'ico'
        ],
    ];

    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        // Add the main UA Compatible header
        if ($config['browsers']) {
            $this->addDirective(new Header('X-UA-Compatible', $config['browsers']));
        }

        // Optionally unset X-UA-Compatible for static files
        if ($config['unsetOnStaticFiles']) {
            $filesMatch = new FilesMatch();
            $filesMatch->process(['pattern' => '\.(' . implode('|', $config['staticFilesExtensions']) . ')$']);
            $filesMatch->addDirective(new Header('X-UA-Compatible', null, 'unset'));
            $this->addDirective($filesMatch);
        }
    }
}
