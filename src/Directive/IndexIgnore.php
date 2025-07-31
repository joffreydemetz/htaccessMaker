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
 * IndexIgnore directive for specifying files and directories to ignore in directory listings.
 * This directive allows you to prevent certain files or directories from being displayed
 * in the directory listing when directory browsing is enabled.
 * It is useful for hiding sensitive files or directories from public view.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class IndexIgnore extends ValueDirective
{
    protected string $name = 'IndexIgnore';
}
