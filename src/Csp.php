<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker;

/** 
 * Csp class for managing Content Security Policy (CSP) directives.
 * This class allows you to define and manage CSP rules for your .htaccess files.
 * It provides methods to add items to specific CSP groups, merge existing policies,
 * and generate a string representation of the CSP rules.
 * 
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Csp
{
    protected array $csp = [];

    public function __construct(array $csp = [])
    {
        foreach ($csp as $group => $items) {
            $this->addToGroup($group, $items);
        }
    }

    public function __toString(): string
    {
        $csp = $this->cleanCspArray($this->csp);

        $cspParts = [];
        foreach ($csp as $group => $values) {
            if (in_array($group, ['child', 'connect', 'default', 'font', 'frame', 'img', 'manifest', 'media', 'object', 'prefetch', 'script', 'style', 'worker'])) {
                $cspParts[] = $group . '-src ' . implode(' ', $values) . ';';
            } else {
                $cspParts[] = $group . ' ' . implode(' ', $values) . ';';
            }
        }

        if (!empty($cspParts)) {
            return implode(' ', $cspParts);
        }

        return '';
    }

    public function merge(array $csp, bool $overwrite = false): self
    {
        foreach ($csp as $group => $values) {
            // Convert string values to arrays for compatibility
            if (is_string($values)) {
                $values = [$values];
            }
            $this->addToGroup($group, $values, $overwrite);
        }

        return $this;
    }

    /**
     * Add items to a CSP group
     */
    public function addToGroup(string $group, array $items, bool $overwrite = false): self
    {
        $self = false;
        $data = false;
        $unsafeInline = false;
        $unsafeEval = false;
        $none = false;

        if ($overwrite || !isset($this->csp[$group])) {
            $this->csp[$group] = [];
        }

        $list = [];
        if (!empty($this->csp[$group])) {
            $self = in_array("'self'", $this->csp[$group]);
            $none = in_array("'none'", $this->csp[$group]);
            $data = in_array("data:", $this->csp[$group]);
            $unsafeInline = in_array("'unsafe-inline'", $this->csp[$group]);
            $unsafeEval = in_array("'unsafe-eval'", $this->csp[$group]);

            foreach ($this->csp[$group] as $item) {
                if (!in_array($item, ["'self'", "'none'", "data:", "'unsafe-inline'", "'unsafe-eval'"])) {
                    $list[] = $item;
                }
            }
        }

        $items = array_unique($items);
        foreach ($items as $i => $item) {
            if ($item === 'self') {
                $item = "'self'";
            } elseif ($item === 'data') {
                $item = "data:";
            } elseif ($item === 'none') {
                $item = "'none'";
            } elseif ($item === 'unsafe-inline') {
                $item = "'unsafe-inline'";
            } elseif ($item === 'unsafe-eval') {
                $item = "'unsafe-eval'";
            }

            if ($item === "'self'") {
                $self = true;
                continue;
            }
            if ($item === "data:") {
                $data = true;
                continue;
            }
            if ($item === "'none'") {
                $none = true;
                continue;
            }
            if ($item === "'unsafe-inline'") {
                $unsafeInline = true;
                continue;
            }
            if ($item === "'unsafe-eval'") {
                $unsafeEval = true;
                continue;
            }

            $list[] = $item;
        }

        $list = array_unique($list);

        $this->csp[$group] = [];
        if (true === $self) {
            $this->csp[$group][] = "'self'";
        }
        if (true === $none) {
            $this->csp[$group][] = "'none'";
        }
        if (true === $unsafeInline) {
            $this->csp[$group][] = "'unsafe-inline'";
        }
        if (true === $unsafeEval) {
            $this->csp[$group][] = "'unsafe-eval'";
        }
        foreach ($list as $item) {
            $this->csp[$group][] = $item;
        }
        if (true === $data) {
            $this->csp[$group][] = "data:";
        }

        return $this;
    }

    private function cleanCspArray(array $csp): array
    {
        $cleaned = [];
        foreach ($csp as $group => $items) {
            $cleaned[$group] = array_unique($items);
            if (empty($cleaned[$group])) {
                unset($cleaned[$group]);
            }
        }
        return $cleaned;
    }
}
