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
use JDZ\CspMaker\CspBuilder;

/**
 * Content Security Policy Container
 *
 * Apache adapter: builds the policy with jdz/cspmaker and emits it as a
 * Content-Security-Policy header. The `csp` config is a directive => source(s)
 * map (short names like `script` are accepted); the optional `integrations`
 * config applies named third-party recipes (e.g. matomo) so no directive gets
 * forgotten.
 *
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class CspContainer extends Container
{
    protected array $defaults = [
        'useXContentSecurityPolicy' => false,
        'csp' => [],
        'integrations' => [],
    ];

    /** Baseline policy merged under whatever the caller supplies. */
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

        // Baseline defaults, then let the caller's config REPLACE per directive
        // (a caller that sets default-src 'none' must win over the 'self'
        // default), then layer integration hosts on top.
        $builder = CspBuilder::create()->merge($this->defaultCspPolicy);

        foreach ((array) ($config['csp'] ?? []) as $directive => $sources) {
            $builder->set((string) $directive, $sources);
        }

        if (!empty($config['integrations'])) {
            $builder->integrations((array) $config['integrations']);
        }

        $headerName = $config['useXContentSecurityPolicy']
            ? 'X-Content-Security-Policy'
            : 'Content-Security-Policy';

        $this->addDirective(new Header($headerName, '"' . $builder->build() . '"'));
    }
}
