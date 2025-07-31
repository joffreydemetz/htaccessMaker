<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive\Header;

/**
 * Anti-XSS Headers Container
 * This container provides directives to set various HTTP headers
 * that help mitigate cross-site scripting (XSS) attacks.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class AntiXSS extends Container
{
    protected array $defaults = [
        'xssProtection' => '1; mode=block',
        'frameOptions' => 'SAMEORIGIN',
        'contentTypeOptions' => 'nosniff',
        'refererPolicy' => 'strict-origin-when-cross-origin',
        'strictTransportSecurity' => '',
    ];

    /**
     * Process configuration to generate anti-XSS security headers
     */
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        if ($config['xssProtection']) {
            $this->addXssProtection($config['xssProtection']);
        }
        if ($config['frameOptions']) {
            $this->addFrameOptions($config['frameOptions']);
        }
        if ($config['contentTypeOptions']) {
            $this->addContentTypeOptions($config['contentTypeOptions']);
        }
        if ($config['strictTransportSecurity']) {
            $this->addStrictTransportSecurity($config['strictTransportSecurity']);
        }
        if ($config['refererPolicy']) {
            $this->addReferrerPolicy($config['refererPolicy']);
        }
    }

    /**
     * Add X-XSS-Protection header
     */
    public function addXssProtection(string $value = '1; mode=block'): self
    {
        $this->addDirective(new Header('X-XSS-Protection', $value));
        return $this;
    }

    /**
     * Add X-Frame-Options header
     */
    public function addFrameOptions(string $value = 'SAMEORIGIN', string $action = 'append'): self
    {
        $h = new Header('X-Frame-Options', $value, $action);
        $h->withAlways();
        $this->addDirective($h);
        return $this;
    }

    /**
     * Add X-Content-Type-Options header
     */
    public function addContentTypeOptions(string $value = 'nosniff'): self
    {
        $this->addDirective(new Header('X-Content-Type-Options', $value));
        return $this;
    }

    /**
     * Add Referrer-Policy header
     */
    public function addReferrerPolicy(string $value = 'strict-origin-when-cross-origin'): self
    {
        $this->addDirective(new Header('Referrer-Policy', $value, 'set', true));
        return $this;
    }

    /**
     * Add Strict-Transport-Security header
     */
    public function addStrictTransportSecurity(string $value = ''): self
    {
        if ($value) {
            $quotedValue = (preg_match('/[\s;=]/', $value)) ? '"' . $value . '"' : $value;
            $h = new Header('Strict-Transport-Security', $quotedValue, 'set');
            $h->withAlways();
            $this->addDirective($h);
        }
        return $this;
    }
}
