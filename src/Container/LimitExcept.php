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
 * LimitExcept
 * 
 * This container is used to limit access to specific HTTP methods
 * for a directory or file in an .htaccess configuration.
 * It allows you to specify which HTTP methods are allowed or denied.
 * 
 * @see https://httpd.apache.org/docs/current/mod/core.html#limitexcept
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class LimitExcept extends Container
{
    protected ?string $tag = 'LimitExcept';
    protected array $authMethods = [];
    protected array $defaults = [
        'authMethods' => [],
    ];

    public function toString(bool $showComments = false, int $indent = 0): string
    {
        if (!$this->authMethods) {
            return '';
        }

        return parent::toString($showComments, $indent);
    }

    protected function parseConfig(array $config): array|false
    {
        if (empty($config['authMethods'])) {
            if ($this->authMethods) {
                $config['authMethods'] = $this->authMethods;
            }
        } else {
            $this->authMethods = $config['authMethods'] ?? [];
        }

        $config = parent::parseConfig($config);

        if ($config && empty($config['authMethods']) && !$this->authMethods) {
            return false;
        }

        if (false === $config) {
            return false;
        }

        return $config;
    }

    public function process(array $config = []): void
    {
        if (empty($config['authMethods']) && $this->authMethods) {
            $config['authMethods'] = $this->authMethods;
        }

        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        $this->authMethods = $config['authMethods'];
    }

    public function attributes(): string
    {
        $this->authMethods = array_unique($this->authMethods);
        return ' ' . implode(' ', $this->authMethods);
    }
}
