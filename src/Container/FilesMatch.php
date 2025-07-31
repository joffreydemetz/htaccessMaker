<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container;

/**
 * FilesMatch
 * 
 * This container is used to match files based on a regular expression pattern.
 * It allows you to apply directives to specific file types or patterns
 * within your .htaccess configuration.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class FilesMatch extends Container
{
    protected ?string $tag = 'FilesMatch';
    protected string $pattern = '';
    protected array $defaults = [
        'pattern' => '',
    ];

    public function toString(bool $showComments = false, int $indent = 0): string
    {
        if (!$this->pattern) {
            return '';
        }

        return parent::toString($showComments, $indent);
    }

    protected function parseConfig(array $config): array|false
    {
        if (!$config) {
            $config['pattern'] = '';
            return false;
        }

        if (empty($config['pattern'])) {
            if ($this->pattern) {
                $config['pattern'] = $this->pattern;
            }
        } else {
            $this->pattern = $config['pattern'] ?? '';
        }

        $config = parent::parseConfig($config);

        if ($config && empty($config['pattern']) && !$this->pattern) {
            $this->pattern = '';
            return false;
        }

        if (false === $config) {
            $this->pattern = '';
            return false;
        }

        return $config;
    }

    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        $this->pattern = $config['pattern'];
    }

    protected function attributes(): string
    {
        return ' "' . $this->pattern . '"';
    }
}
