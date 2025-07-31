<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\Directive\ExpiresDefault;
use JDZ\HtaccessMaker\Directive\ExpiresByType;
use JDZ\HtaccessMaker\IfModule;

/**
 * Expires Module
 * 
 * This module is responsible for enabling and configuring
 * the Expires headers in the .htaccess file. It allows you to set
 * caching policies for different types of content.
 * 
 * It can be used to specify how long browsers should cache
 * static resources like images, CSS, and JavaScript files.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ExpiresModule extends IfModule
{
    protected array $defaults = [
        'cacheRules' => [],
        'defaultExpiry' => null,
        'useCommonRules' => true,
    ];

    public function __construct()
    {
        parent::__construct('mod_expires.c');

        $this->addDirective('ExpiresActive On');
    }

    /**
     * Process configuration to generate expires directives
     */
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        // Add default expiry if provided
        if ($config['defaultExpiry']) {
            $this->addDefaultExpiry($config['defaultExpiry']);
        }

        // Add specific cache rules if provided
        if ($config['cacheRules']) {
            $this->addExpiryRules($config['cacheRules']);
        }

        // Add common cache rules if no custom rules provided or if explicitly requested
        elseif ($config['useCommonRules']) {
            $this->addCommonRules();
        }

        // Add default expiry if not already set and no custom default provided
        if (!$config['defaultExpiry'] && empty($config['cacheRules']) && $config['useCommonRules']) {
            $this->addDefaultExpiry('access plus 2 days');
        }
    }

    /**
     * Add default expiry time for all files
     */
    public function addDefaultExpiry(string $expiry): self
    {
        $this->addDirective(new ExpiresDefault($expiry));
        return $this;
    }

    /**
     * Add expiry rule for specific MIME type
     */
    public function addExpiryByType(string $mimeType, string $expiry): self
    {
        $this->addDirective(new ExpiresByType($mimeType, $expiry));
        return $this;
    }

    /**
     * Add multiple expiry rules from array
     */
    public function addExpiryRules(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->addExpiryByType($rule['mimeType'], $rule['expiry']);
        }
        return $this;
    }

    /**
     * Add common expiry rules for web assets
     * Sets standard caching times for CSS, JS, images, fonts, etc.
     */
    public function addCommonRules(): self
    {
        $commonRules = [
            ['mimeType' => 'image/jpg', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'image/jpeg', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'image/gif', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'image/png', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'text/css', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'text/javascript', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'application/pdf', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'application/javascript', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'application/x-javascript', 'expiry' => 'access plus 1 month'],
            ['mimeType' => 'image/x-icon', 'expiry' => 'access plus 1 year'],
        ];

        $this->addExpiryRules($commonRules);
        return $this;
    }
}
