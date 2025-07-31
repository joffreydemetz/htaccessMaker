<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Directive\IndexIgnore;
use JDZ\HtaccessMaker\Directive\Options;

/** 
 * Negotiation module 
 * 
 * Handles content negotiation settings in .htaccess files.
 * It allows enabling/disabling MultiViews, setting index ignore patterns,
 * and adding custom options.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class NegociationModule extends IfModule
{
    public function __construct()
    {
        parent::__construct('mod_negotiation.c');
    }

    public function process(array $config = []): void
    {
        $config = array_merge([
            'multiViews' => false,
            'indexIgnore' => '*',
            'customOptions' => [],
        ], $config);

        // Handle MultiViews setting
        if ($config['multiViews']) {
            $this->enableMultiViews();
        } else {
            $this->disableMultiViews();
        }

        // Add custom options if provided
        if ($config['customOptions']) {
            $this->addDirective(new Options($config['customOptions']));
        }

        // Add index ignore if specified
        if ($config['indexIgnore']) {
            $this->addIndexIgnore($config['indexIgnore']);
        }
    }

    /**
     * Disable MultiViews option
     */
    public function disableMultiViews(): self
    {
        $this->addDirective(new Options(['-MultiViews']));
        return $this;
    }

    /**
     * Enable MultiViews option
     */
    public function enableMultiViews(): self
    {
        $this->addDirective(new Options(['+MultiViews']));
        return $this;
    }

    /**
     * Add index ignore directive
     */
    public function addIndexIgnore(string $pattern = '*'): self
    {
        $this->addDirective(new IndexIgnore($pattern));
        return $this;
    }
}
