<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\Module\RewriteModule;

/**
 * ForceSecureRewrite
 * 
 * This module forces HTTPS by rewriting HTTP requests to HTTPS.
 * It allows you to specify paths that should be excluded from this rewrite.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ForceSecureRewrite extends RewriteModule
{
    protected array $defaults = [
        'excludePaths' => [],
    ];

    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        foreach ($config['excludePaths'] as $path) {
            $this->addRewriteCond('%{REQUEST_URI}', '!^' . $path, ['NC']);
        }
    }
}
