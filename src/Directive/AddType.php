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
 * AddType directive for specifying MIME types in .htaccess files.
 * This directive allows you to associate a MIME type with a file extension,
 * enabling the server to serve files with the correct content type.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class AddType extends ValueDirective
{
    protected string $name = 'AddType';
}
