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
 * ExpiresDefault directive for setting default expiration times in .htaccess files.
 * This directive allows you to specify a default expiration time for resources that do not have a specific expiration set.
 * It helps in controlling caching behavior for resources that do
 * not have a defined expiration, ensuring that they are cached appropriately by browsers and proxies.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ExpiresDefault extends Directive
{
    protected string $name = 'ExpiresDefault';
    private string $expires;

    public function __construct(string $expires)
    {
        $this->expires = $expires;
    }

    public function value(): string
    {
        return '"' . $this->expires . '"';
    }
}
