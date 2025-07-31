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
 * ExpiresByType directive for setting expiration times for specific MIME types in .htaccess files.
 * This directive allows you to specify how long resources of a certain type should be cached by the
 * browser or proxy, helping to improve performance and reduce server load.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ExpiresByType extends Directive
{
    protected string $name = 'ExpiresByType';
    private string $type;
    private string $expires;

    public function __construct(string $type, string $expires)
    {
        $this->type = $type;
        $this->expires = $expires;
    }

    public function value(): string
    {
        return $this->type . ' "' . $this->expires . '"';
    }
}
