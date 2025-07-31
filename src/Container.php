<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker;

use JDZ\HtaccessMaker\IfModule;

/** 
 * Container class for managing directives and nested containers in .htaccess files.
 * This class allows you to create a structured representation of .htaccess files,
 * making it easier to generate and manipulate .htaccess configurations programmatically.
 * It supports adding directives, nested containers, and controlling the visibility of comments.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Container
{
  protected ?string $tag = null;
  protected bool $ignoreTag = false;
  protected array $directives = [];
  protected array $defaults = [];

  public function addDirective(Directive|Container|string $directive): self
  {
    $this->directives[] = $directive;
    return $this;
  }

  public function ensureApacheCompatibility(): self
  {
    foreach ($this->directives as $directive) {
      if ($directive instanceof Container) {
        $directive->ensureApacheCompatibility();
      }
    }

    return $this;
  }

  public function process(array $config = []): void
  {
    $config = $this->parseConfig($config);

    if (false === $config) {
      return;
    }
  }

  /**
   * Convert the container and its directives to a string representation.
   * A container is prefixed and followed by a new line.
   */
  public function toString(bool $showComments = true, int $indent = 0): string
  {
    $tag = $this->tag && false === $this->ignoreTag ? $this->tag : null;

    $isFirstDirective = true;
    $containerDirectives = '';
    foreach ($this->directives as $directive) {
      $directiveString = $this->directiveToString($directive, $showComments, $tag ? $indent + 1 : $indent);

      if (!$directiveString || empty(trim($directiveString))) {
        continue;
      }

      // Add a new line before the directive if it's not the first one
      if (!$isFirstDirective) {
        $containerDirectives .= "\n";
      }

      $containerDirectives .= $directiveString;
      $isFirstDirective = false;
    }

    if (!$containerDirectives || empty(trim($containerDirectives))) {
      return '';
    }

    $indentStr = str_repeat('  ', $indent);

    $str = '';

    if ($tag) {
      $str .= $indentStr . '<' . $tag . $this->attributes() . '>' . "\n";
    }

    $str .= $containerDirectives . "\n";

    if ($tag) {
      $str .= $indentStr . '</' . $tag . '>' . "\n";
    }

    if (!$str || empty(trim($str))) {
      return '';
    }

    return $str . "\n";
  }

  protected function parseConfig(array $config): array|false
  {
    if (!empty($config)) {
      $config['enabled'] = true;
    }

    $config = array_merge([
      'enabled' => false,
    ], $config);

    $config = array_merge($this->defaults, $config);

    if (!$config['enabled']) {
      return false;
    }

    return $config;
  }

  protected function attributes(): string
  {
    return '';
  }

  private function directiveToString(Directive|Container|string $directive, bool $showComments, int $indent): string
  {
    if (is_string($directive)) {
      if (!$showComments && str_starts_with(trim($directive), '#')) {
        return '';
      }
      return str_repeat('  ', $indent) . $directive;
    }

    return $directive->toString($showComments, $indent);
  }
}
