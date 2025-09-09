<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\Module\RewriteModule;
use JDZ\HtaccessMaker\Directive\Comment;

/**
 * Redirects all requests from www to non-www.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class RedirectWwwRewrite extends RewriteModule
{
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        $this->addDirective(new Comment('Redirect www to non-www'));
        $this->addRewriteCond('%{HTTP_HOST}', '^www\.(.*)$');
        $this->addRewriteRule('^(.*)$', 'https://%1/$1', ['R=301', 'L']);
    }
}
