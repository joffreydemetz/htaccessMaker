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
 * ErrorDocument directive for handling error responses in .htaccess files.
 * This directive allows you to specify custom error pages for different HTTP status codes.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ErrorDocument extends Directive
{
    protected string $name = 'ErrorDocument';

    private int $code;
    private ?string $value = null;

    public function __construct(int $code, ?string $value = null)
    {
        $this->code = $code;
        $this->value = $value;
    }

    protected function value(): string
    {
        return $this->code . ' ' . $this->value;
    }
}
