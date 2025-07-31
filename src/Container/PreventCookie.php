<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container\FilesMatch;
use JDZ\HtaccessMaker\Directive\Header;
use JDZ\HtaccessMaker\Directive\FileETag;
use JDZ\HtaccessMaker\Directive\Comment;

/**
 * PreventCookie on FilesMatch
 * 
 * Handles settings to prevent cookies from being set for static files.
 * It allows setting cache control headers, removing cookies, and disabling ETags.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class PreventCookie extends FilesMatch
{
    protected string $pattern = '\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$';

    protected array $defaults = [
        'maxAge' => 31536000, // Default to 1 year
        'removeCookies' => true,
        'setCacheControl' => true,
        'setConnectionHeaders' => true,
        'disableETags' => true,
        'setVaryHeaders' => true,
    ];

    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        // Remove cookies
        if ($config['removeCookies']) {
            $this->removeCookies();
        }

        // Set cache control
        if ($config['setCacheControl']) {
            $this->addCacheControlHeader('public, max-age=' . $config['maxAge']);
        }

        // Set vary headers
        if ($config['setVaryHeaders']) {
            $this->setVaryHeaders(['Accept-Encoding', 'User-Agent']);
        }

        // Set connection headers
        if ($config['setConnectionHeaders']) {
            $this->setConnectionHeaders([
                'Connection' => 'Keep-Alive',
                'Keep-Alive' => '"timeout=5, max=100"',
            ]);
        }

        // Disable ETags
        if ($config['disableETags']) {
            $this->disableETags();
        }
    }

    /**
     * Add Cache-Control header with specified value
     */
    public function addCacheControlHeader(string $value): self
    {
        $this->addDirective(new Header('Cache-Control', $value, 'set'));
        return $this;
    }

    /**
     * Remove cookie headers from static file responses
     */
    public function removeCookies(): self
    {
        $this->addDirective(new Header('Cookie', null, 'unset'));
        $this->addDirective(new Header('Set-Cookie', null, 'unset'));
        return $this;
    }

    /**
     * Disable ETags for static files
     */
    public function disableETags(): self
    {
        $this->addDirective(new FileETag('None'));
        return $this;
    }

    /**
     * Set Vary headers for proper caching behavior
     */
    public function setVaryHeaders(array $headers): self
    {
        foreach ($headers as $header) {
            $h = new Header('', $header, 'set');
            $h->withVary();
            $this->addDirective($h);
        }
        return $this;
    }

    /**
     * Set connection headers for persistent connections
     */
    public function setConnectionHeaders(array $headers): self
    {
        foreach ($headers as $header => $value) {
            $this->addDirective(new Header($header, $value, 'append'));
        }
        return $this;
    }
}
