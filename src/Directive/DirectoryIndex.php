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
 * DirectoryIndex directive for specifying the default index files in .htaccess files.
 * This directive allows you to set the order of index files that the server will look for
 * when a directory is requested.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class DirectoryIndex extends ValueDirective
{
    protected string $name = 'DirectoryIndex';
}
