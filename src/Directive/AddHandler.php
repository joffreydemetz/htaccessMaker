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
 * AddHandler directive for specifying handlers for file extensions in .htaccess files.
 * This directive allows you to associate a handler with file extensions,
 * enabling the server to process files with specific extensions using the specified handler.
 * 
 * Example usage:
 * - new AddHandler('cgi-script', ['.cgi', '.pl', '.py'])
 * - new AddHandler('server-parsed', ['.shtml', '.shtm'])
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class AddHandler extends ValueDirective
{
    protected string $name = 'AddHandler';

    public function __construct(string $handler,  array $extensions = [])
    {
        if ($extensions) {
            $value = $handler . ' ' . implode(' ', $extensions);
        } else {
            $value = '';
        }

        parent::__construct($value);
    }
}
