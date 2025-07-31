<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\Directive\Header;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\IfModule;

/**
 * Deflate Module
 * 
 * This module is responsible for enabling and configuring
 * output compression using mod_deflate.
 * 
 * It allows you to specify MIME types, browser compatibility,
 * and other settings to optimize the compression process.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class DeflateModule extends IfModule
{
    protected bool $ignoreTag = false;
    protected array $defaults = [
        'mimeTypes' => ['text/html', 'text/css', 'application/javascript', 'application/json'],
        'browserCompatibility' => true,
        'fileExclusions' => [],
        'varyHeader' => true,
    ];

    public function __construct()
    {
        parent::__construct('mod_deflate.c');

        $this->addDirective('SetOutputFilter DEFLATE');
    }

    /**
     * Process configuration to generate deflate/compression directives
     */
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        // Add MIME types
        if ($config['mimeTypes']) {
            $this->addMimeTypes($config['mimeTypes']);
        }

        // Add browser compatibility rules
        if ($config['browserCompatibility']) {
            $this->addBrowserCompatibility();
        }

        // Add file exclusions
        if ($config['fileExclusions']) {
            $this->addFileExclusions($config['fileExclusions']);
        }

        // Add Vary header
        if ($config['varyHeader']) {
            $this->addVaryHeader();
        }
    }

    /**
     * Add MIME types for compression
     */
    public function addMimeTypes(array $mimeTypes): self
    {
        $mimeTypesString = implode(' ', $mimeTypes);
        $this->addDirective('AddOutputFilterByType DEFLATE ' . $mimeTypesString);
        return $this;
    }

    /**
     * Add browser compatibility rules for compression
     * Handles older browsers that don't support compression properly
     */
    public function addBrowserCompatibility(): self
    {
        $this->addDirective(new Comment('For incompatible browsers'));
        $this->addDirective('BrowserMatch ^Mozilla/4 gzip-only-text/html');
        $this->addDirective('BrowserMatch ^Mozilla/4\.0[678] no-gzip');
        $this->addDirective('BrowserMatch \bMSIE !no-gzip !gzip-only-text/html');
        $this->addDirective('BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html');

        return $this;
    }

    /**
     * Exclude already compressed files
     */
    public function addFileExclusions(array $files = []): self
    {
        $this->addDirective(new Comment('Do not compress these files'));

        $extensions = ['gif', 'jpe?g', 'png', 'ico', 'zip', 'gz', 'pdf'];
        $pattern = '\.(?:' . implode('|', $extensions) . ')$';
        $this->addDirective('SetEnvIfNoCase Request_URI ' . $pattern . ' no-gzip');

        foreach ($files as $file) {
            $this->addDirective('SetEnvIfNoCase Request_URI ^' . preg_quote($file, '/') . '$ no-gzip dont-vary');
        }

        return $this;
    }

    /**
     * Add Vary header for proxies
     */
    public function addVaryHeader(): self
    {
        $h = new Header('Vary', 'Accept-Encoding', 'append');
        $h->withVary();
        $h->setCondition('!dont-vary');
        $this->addDirective($h);

        return $this;
    }
}
