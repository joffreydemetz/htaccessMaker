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
 * Comment directive for adding comments in .htaccess files.
 * This directive allows you to insert comments that can help document the configuration.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Comment extends Directive
{
    protected string $comment;
    protected string $border;

    public function __construct(string $comment, string $border = '')
    {
        $this->comment = $comment;
        $this->border = $border;
    }

    public function toString(bool $showComments = true, int $indent = 0): string
    {
        if (!$showComments && !$this->forceComment) {
            return '';
        }

        if (!$this->comment) {
            return '';
        }

        $comment = explode("\n", trim($this->comment));
        $comment = array_map(fn($line) => trim($line), $comment);
        $comment = array_filter($comment, fn($line) => !empty($line));

        if (empty($comment)) {
            return '';
        }

        $indentStr = str_repeat('  ', $indent);

        $output = '';

        if ($this->border) {
            $output .= $indentStr . "\n";
            $output .= $indentStr . $this->border . "\n";
        }

        $output .= implode("\n", array_map(fn($line) => $indentStr . '# ' . $line, $comment));

        if ($this->border) {
            $output .= "\n";
            $output .= $indentStr . $this->border . "\n";
            $output .= $indentStr . "\n";
        }

        return $output;
    }
}
