<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Directive;

use JDZ\HtaccessMaker\Directive;

/** 
 * ValueDirective class for directives that have a value.
 * This class extends the base Directive class and provides a way to handle directives
 * that require a value to be set.
 * It is used for directives that need to store a specific value, such as configuration settings,
 * paths, or other parameters that are essential for the directive's functionality.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ValueDirective extends Directive
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toString(bool $showComments = true, int $indent = 0): string
    {
        if (empty(trim($this->value()))) {
            return '';
        }

        return parent::toString($showComments, $indent);
    }
}
