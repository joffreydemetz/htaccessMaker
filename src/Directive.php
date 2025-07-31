<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker;

/** 
 * Directive class for representing a directive in .htaccess files.
 * This class serves as a base for all directives, providing common functionality
 * such as setting the directive name, controlling comment visibility,
 * and generating a string representation of the directive.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Directive
{
    protected string $name = '';
    protected bool $forceComment = false;

    public function setName(string $name = ''): self
    {
        $this->name = $name;
        return $this;
    }

    public function setForceComment(bool $forceComment = true): self
    {
        $this->forceComment = $forceComment;
        return $this;
    }

    public function toString(bool $showComments = true, int $indent = 0): string
    {
        $value = $this->value();

        if (!$this->name && !$value) {
            return '';
        }

        $indentStr = str_repeat('  ', $indent);

        if ($this->forceComment) {
            return $indentStr . '# ' . $this->name . ' ' . $value;
        }

        return $indentStr . $this->name . ' ' . $value;
    }

    protected function value(): string
    {
        return '';
    }
}
