<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker;

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive;
use JDZ\HtaccessMaker\EmptyLine;

/**
 * HtAccess
 * 
 * This class represents an .htaccess file and provides methods to add directives,
 * set comments, and convert the directives to a string representation.
 * It allows for easy management of .htaccess directives, including conditional directives
 * and directives with specific attributes.
 * 
 * @param bool $showComments  Whether to show comments in the output.
 * @param bool $ensureApacheCompatibility Whether to ensure compatibility using IfModule.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class HtAccess
{
  protected array $directives = [];
  protected bool $showComments = true;
  protected bool $ensureApacheCompatibility = false;

  public function toString(): string
  {
    $str = '';

    foreach ($this->directives as $directive) {
      $directiveOutput = $this->directiveToString($directive);

      // Skip empty directives or comments if showComments is false
      if (!$directiveOutput || empty(trim($directiveOutput))) {
        if ($directive instanceof EmptyLine) {
          $str .= "\n";
        }
        continue;
      }

      $str .= $directiveOutput . "\n";
    }

    // Replace multiple consecutive empty lines with single empty lines
    return preg_replace('/\n{3,}/', "\n\n", $str);
  }

  public function withComments(bool $showComments = true): self
  {
    $this->showComments = $showComments;
    return $this;
  }

  public function withApacheCompatibility(bool $ensure = true): self
  {
    $this->ensureApacheCompatibility = $ensure;
    return $this;
  }

  public function addDirective(Directive|Container|string $directive): self
  {
    $this->directives[] = $directive;
    return $this;
  }

  private function directiveToString(Directive|Container|string $directive): string
  {
    return self::stringifyDirective(
      $directive,
      $this->showComments,
      $this->ensureApacheCompatibility
    );
  }

  public static function stringifyDirective(Directive|Container|string $directive, bool $showComments = true, bool $ensureApacheCompatibility = true, int $indent = 0): string
  {
    if (is_string($directive)) {
      if (!$showComments && str_starts_with(trim($directive), '#')) {
        return '';
      }
      return $directive;
    }

    if ($directive instanceof Container) {
      if ($ensureApacheCompatibility) {
        $directive->ensureApacheCompatibility();
      }
    }

    return $directive->toString($showComments, $indent);
  }
}
