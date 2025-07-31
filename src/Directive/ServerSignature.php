<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Directive;

use JDZ\HtaccessMaker\Directive\ValueDirective;

/** 
 * ServerSignature directive for controlling the server signature in .htaccess files.
 * This directive allows you to enable or disable the server signature, which is a footer that
 * is added to server-generated pages, displaying information about the server and its configuration.
 * 
 * @see https://httpd.apache.org/docs/current/mod/core.html#serversignature
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ServerSignature extends ValueDirective
{
    protected string $name = 'ServerSignature';
}
