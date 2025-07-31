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
 * RewriteEngine directive for enabling or disabling the rewrite engine in .htaccess files.
 * This directive allows you to control whether the rewrite engine is active or not.
 * It is essential for enabling URL rewriting features in Apache, allowing you to create cleaner and more user-friendly URLs.
 * 
 * @see https://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewriteengine
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class RewriteEngine extends Directive
{
    protected string $name = 'RewriteEngine';
    protected string $status;

    public function __construct(string $status = 'On')
    {
        $this->status = strtolower($status) === 'off' ? 'Off' : 'On';
    }

    protected function value(): string
    {
        return $this->status;
    }
}
