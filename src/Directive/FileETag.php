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
 * FileETag directive for controlling the ETag generation for files in .htaccess files.
 * This directive allows you to specify how ETags are generated for files, which can be useful
 * for cache validation and ensuring that clients receive the correct version of a resource.
 * 
 * @see https://httpd.apache.org/docs/current/mod/core.html#fileetag
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class FileETag extends ValueDirective
{
    protected string $name = 'FileETag';
}
