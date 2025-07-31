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
 * RewriteRule directive for defining rewrite rules in .htaccess files.
 * This directive allows you to specify rules for rewriting URLs, which can be used to create cleaner
 * and more user-friendly URLs, redirect requests, or perform other URL manipulations.
 * 
 * @see https://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewriterule
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class RewriteRule extends Directive
{
    protected string $name = 'RewriteRule';
    protected string $pattern;
    protected string $substitution;
    protected array $flags;

    public function __construct(string $pattern, string $substitution, array $flags = [])
    {
        $this->pattern = $pattern;
        $this->substitution = $substitution;
        $this->flags = $flags;
    }

    protected function value(): string
    {
        $value = $this->pattern . ' ' . $this->substitution;

        if (!empty($this->flags)) {
            $value .= ' [' . implode(',', $this->flags) . ']';
        }

        return $value;
    }
}
