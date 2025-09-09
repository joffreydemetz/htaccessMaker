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
use JDZ\HtaccessMaker\EmptyLine;

/** 
 * MaintenanceRewrite
 * 
 * Maintenance mode RewriteModule for controlled site access.
 * It allows you to specify allowed IPs and a maintenance page.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class MaintenanceRewrite extends RewriteModule
{
    protected array $defaults = [
        'allowedIps' => [],
        'maintenanceFile' => '/maintenance.html',
        'defaultState' => false,
    ];

    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        // Add maintenance ON section (commented if disabled)
        $onComment = $config['defaultState'] ? '' : '# ';
        //$this->addDirective(new Comment('Maintenance ON'));

        // Add IP exclusions
        foreach ($config['allowedIps'] as $ip) {
            $escapedIp = str_replace('.', '\.', $ip);
            $this->addDirective($onComment . 'RewriteCond %{REMOTE_ADDR} !^' . $escapedIp . '$');
        }

        // Exclude the maintenance page itself
        $this->addDirective($onComment . 'RewriteCond %{REQUEST_URI} !^' . $config['maintenanceFile'] . '$');
        $this->addDirective($onComment . 'RewriteRule $ ' . $config['maintenanceFile'] . ' [L]');

        $this->addDirective(new EmptyLine());

        // Add maintenance OFF section (commented if enabled)
        $offComment = $config['defaultState'] ? '# ' : '';
        //$this->addDirective(new Comment('Maintenance OFF'));
        $this->addDirective($offComment . 'RewriteCond %{REQUEST_URI} ^' . $config['maintenanceFile'] . '$');
        $this->addDirective($offComment . 'RewriteRule ^ https://%{HTTP_HOST}/ [L,R=301]');
    }
}
