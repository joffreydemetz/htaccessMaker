<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker;

use JDZ\HtaccessMaker\Container;

/** 
 * IfModule
 * 
 * This container is used to conditionally include directives
 * based on the presence of a specific Apache module.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class IfModule extends Container
{
    protected ?string $tag = 'IfModule';
    protected string $module;
    protected bool $ignoreTag = true;

    public function __construct(string $module)
    {
        $this->module = $module;
    }

    public function withIgnoreTag(bool $ignoreTag = true): self
    {
        $this->ignoreTag = $ignoreTag;
        return $this;
    }

    public function ensureApacheCompatibility(): self
    {
        $this->ignoreTag = false;
        return parent::ensureApacheCompatibility();
    }

    public function attributes(): string
    {
        return ' ' . $this->module;
    }
}
