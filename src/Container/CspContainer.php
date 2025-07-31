<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Csp;
use JDZ\HtaccessMaker\Directive\Header;

/**
 * Content Security Policy Container
 * 
 * This container is responsible for setting the Content Security Policy (CSP)
 * headers in the .htaccess file. It allows you to define a default CSP policy
 * and merge it with custom configurations.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class CspContainer extends Container
{
    protected array $defaults = [
        'useXContentSecurityPolicy' => false,
        'csp' => [],
    ];

    private array $defaultCspPolicy = [
        'default' => ['self'],
        'script' => ['self'],
        'style' => ['self'],
        'font' => ['self'],
        'connect' => ['self'],
        'frame' => [],
        'img' => ['self', 'data'],
        'child' => ['self'],
        'media' => ['self'],
        'object' => ['self'],
        'manifest' => ['self'],
    ];

    /**
     * Process configuration to generate Content Security Policy headers
     */
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        $csp = new Csp($this->defaultCspPolicy);

        // Merge with custom config if provided
        if ($config['csp']) {
            $csp->merge($config['csp'], true);
        }

        // Choose header type based on config
        $headerName = $config['useXContentSecurityPolicy']
            ? 'X-Content-Security-Policy'
            : 'Content-Security-Policy';

        // Add CSP header directive
        $this->addDirective(new Header($headerName, '"' . (string)$csp . '"'));
    }
}
