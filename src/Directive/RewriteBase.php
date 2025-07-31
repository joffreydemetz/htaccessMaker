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
 * RewriteBase directive for setting the base URL for rewrite rules in .htaccess files.
 * This directive specifies the base URL path for the rewrite rules, which is useful when the .htaccess
 * file is not located in the root directory of the website.
 * It helps in correctly interpreting the rewrite rules relative to the specified base URL.
 *
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class RewriteBase extends Directive
{
    protected string $name = 'RewriteBase';
    protected string $urlPath;

    public function __construct(string $urlPath)
    {
        if ($urlPath !== '/' && !str_ends_with($urlPath, '/')) {
            $urlPath .= '/';
        }

        $this->urlPath = $urlPath;
    }

    protected function value(): string
    {
        return $this->urlPath;
    }
}
