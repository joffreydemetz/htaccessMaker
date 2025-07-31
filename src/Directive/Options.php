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
 * Options directive for configuring various server options in .htaccess files.
 * This directive allows you to enable or disable specific features or behaviors
 * of the server, such as following symbolic links, enabling CGI scripts, or allowing overrides.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Options extends Directive
{
    protected string $name = 'Options';

    private array $options = [];

    public function __construct(array|string $options = [])
    {
        if (is_string($options)) {
            $options = explode(' ', $options);
        }
        $this->options = $options;
    }

    public function value(): string
    {
        return implode(' ', array_unique($this->options));
    }
}
