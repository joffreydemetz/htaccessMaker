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
 * Header directive for managing HTTP headers in .htaccess files.
 * This directive allows you to set, modify, or remove HTTP headers for responses.
 * It can be used to control caching, security policies, and other aspects of HTTP responses.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Header extends Directive
{
    protected string $name = 'Header';

    private string $header;
    private ?string $value;
    private string $action;
    private bool $always = false;
    private bool $vary = false;
    private string $condition = '';

    public function __construct(string $header = '', ?string $value = null, string $action = '')
    {
        $this->header = $header;
        $this->value = $value;
        $this->action = $action ? $action : 'set';
    }

    public function withAlways(bool $always = true): self
    {
        $this->always = $always;
        return $this;
    }

    public function withVary(bool $vary = true): self
    {
        $this->vary = $vary;
        return $this;
    }

    public function setCondition(string $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    public function value(): string
    {
        if ('unset' === $this->action) {
            $this->value = null;
        }

        $parts = [];

        if ($this->always) {
            $parts[] = 'always';
        }

        $parts[] = $this->action;

        if ($this->vary) {
            $parts[] = 'vary';
        }

        if ($this->header) {
            $parts[] = $this->header;
        }

        if (null !== $this->value) {
            $parts[] = $this->quoteIfNeeded($this->value);
        }

        if ($this->condition) {
            $parts[] = $this->condition;
        }

        return implode(' ', $parts);
    }

    /**
     * Add quotes around header values if needed based on Apache rules
     */
    private function quoteIfNeeded(string $value): string
    {
        // If already quoted, leave as-is
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return $value;
        }

        if ($value === '') {
            return '""';
        }

        // Values that need quotes: contain spaces, semicolons, commas, hyphens, special chars
        if (preg_match('/[\s;,=\'"()<>\-\/]/', $value)) {
            return '"' . $value . '"';
        }

        // Single words like DENY, SAMEORIGIN, nosniff don't need quotes
        return $value;
    }
}
