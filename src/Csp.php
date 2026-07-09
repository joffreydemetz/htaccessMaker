<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker;

use JDZ\CspMaker\Policy;

/**
 * Backward-compatible shim over {@see \JDZ\CspMaker\Policy}.
 *
 * The CSP building logic now lives in jdz/cspmaker (fluent builder, config
 * files, integration recipes). This subclass keeps the old htaccessMaker API
 * (`addToGroup()`, `merge($csp, $overwrite)`) working.
 *
 * @deprecated Use JDZ\CspMaker\CspBuilder / JDZ\CspMaker\Policy directly.
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Csp extends Policy
{
    /**
     * @param string[] $items
     */
    public function addToGroup(string $group, array $items, bool $overwrite = false): self
    {
        return $overwrite ? $this->set($group, $items) : $this->add($group, $items);
    }

    /**
     * @param array<string,string|array> $csp
     */
    public function merge(array $csp, bool $overwrite = false): self
    {
        foreach ($csp as $group => $values) {
            $this->addToGroup((string) $group, (array) $values, $overwrite);
        }

        return $this;
    }
}
