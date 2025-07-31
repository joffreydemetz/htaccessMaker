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
 * RewriteCond directive for defining conditions for rewrite rules in .htaccess files.
 * This directive allows you to specify conditions that must be met for a rewrite rule to be applied
 * or to control the behavior of the rewrite engine.
 * It is useful for creating complex rewrite rules that depend on specific conditions, such as checking
 * the request method, user agent, or other request headers.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class RewriteCond extends Directive
{
    protected string $name = 'RewriteCond';
    protected string $testString;
    protected string $condPattern;
    protected array $flags;

    public function __construct(string $testString, string $condPattern, array $flags = [])
    {
        $this->testString = $testString;
        $this->condPattern = $condPattern;
        $this->flags = $flags;
    }

    protected function value(): string
    {
        $value = $this->testString . ' ' . $this->condPattern;

        if (!empty($this->flags)) {
            $value .= ' [' . implode(',', $this->flags) . ']';
        }

        return $value;
    }
}
