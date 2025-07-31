<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Directive\RewriteEngine;
use JDZ\HtaccessMaker\Directive\RewriteCond;
use JDZ\HtaccessMaker\Directive\RewriteRule;
use JDZ\HtaccessMaker\Directive\RewriteBase;
use JDZ\HtaccessMaker\Directive\Comment;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class RewriteModule extends IfModule
{
    public function __construct()
    {
        parent::__construct('mod_rewrite.c');

        $this->addDirective(new RewriteEngine('On'));
    }

    public function addRewriteBase(string $urlPath): self
    {
        // Only add RewriteBase if not root
        $this->addDirective(new RewriteBase($urlPath));

        return $this;
    }

    public function addRewriteCond(string $testString, string $condPattern, array $flags = []): RewriteCond
    {
        $rewriteCond = new RewriteCond($testString, $condPattern, $flags);
        $this->addDirective($rewriteCond);
        return $rewriteCond;
    }

    public function addRewriteRule(string $pattern, string $substitution, array $flags = []): RewriteRule
    {
        $rewriteRule = new RewriteRule($pattern, $substitution, $flags);
        $this->addDirective($rewriteRule);
        return $rewriteRule;
    }

    public function addMaintenanceMode(array $allowedIps = [], string $maintenancePage = '/maintenance.html'): self
    {
        $this->addDirective(new Comment('Maintenance mode'));

        // Add IP exclusions - these should always be commented for manual toggle
        foreach ($allowedIps as $ip) {
            $rewriteCond = $this->addRewriteCond('%{REMOTE_ADDR}', '!^' . str_replace('.', '\.', $ip) . '$');
            $rewriteCond->setForceComment(true);
        }

        // Exclude the maintenance page itself - also force commented
        $maintenanceCond = $this->addRewriteCond('%{REQUEST_URI}', '!^' . $maintenancePage . '$');
        $maintenanceCond->setForceComment(true);

        $maintenanceRule = $this->addRewriteRule('$', $maintenancePage, ['L']);
        $maintenanceRule->setForceComment(true);

        $this->addDirective(new Comment('or not to maintenance'));
        $this->addRewriteCond('%{REQUEST_URI}', '^' . $maintenancePage . '$');
        $this->addRewriteRule('^', 'https://%{HTTP_HOST}', ['L']);

        return $this;
    }
}
