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
 * Routing 
 * 
 * Handles URL routing and rewriting for web applications.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class RoutingRewrite extends RewriteModule
{
    public function process(array $config = []): void
    {
        $config = array_merge([
            'baseUrl' => '/',
            'checkTrailingSlash' => true,
            'versionedFiles' => true,
            'mindWellKnown' => false,
            'domainApps' => [],
            'rules' => [],
            'defaultController' => 'index.php',
        ], $config);

        $this->addRewriteBase($config['baseUrl']);

        if ($config['checkTrailingSlash']) {
            $this->checkTrailingSlash($config['mindWellKnown']);
        }

        // Append custom redirect rules if provided
        if ($config['rules']) {
            $this->appendRules($config['rules']);
        }

        if ($config['versionedFiles']) {
            $this->addVersionedFiles();
        }

        foreach ($config['domainApps'] as $domainApp) {
            $this->addDomainApp($domainApp['domain'], $domainApp['file'], $config['mindWellKnown']);
        }

        $this->addDefaultController($config['defaultController'], $config['mindWellKnown']);
    }

    public function checkTrailingSlash(bool $mindWellKnown = false): self
    {
        $this->addDirective(new Comment('Check trailing slash'));
        $this->addRewriteCond('%{REQUEST_URI}', '!/$');
        if ($mindWellKnown) {
            $this->addRewriteCond('%{REQUEST_URI}', '!^/.well-known/?.*');
        }
        $this->addRewriteCond('%{REQUEST_FILENAME}', '!-f');
        $this->addRewriteRule('(.*)$', '/$1/', ['L', 'R=301']);
        return $this;
    }

    public function appendRules(array $redirects): self
    {
        if (!$redirects) {
            return $this;
        }

        $this->addDirective(new Comment('Route redirects'));
        foreach ($redirects as $redirect) {
            if (is_string($redirect)) {
                $this->addDirective($redirect);
            } else {
                $this->addRewriteRule($redirect['from'], $redirect['to'], $redirect['flags'] ?? ['R=301', 'L']);
            }
        }

        return $this;
    }

    public function addVersionedFiles(): self
    {
        $this->addDirective(new Comment('Rewrite versioned files'));
        $this->addRewriteCond('%{QUERY_STRING}', '!(^|&)v=([^&]+)');
        $this->addRewriteRule('^(.*)$', '$1?v=$2', ['L']);
        return $this;
    }

    public function addDomainApp(string $domain, string $appFile, bool $mindWellKnown = false): self
    {
        $this->addDirective(new Comment('App ' . basename($appFile) . ' controller'));
        $this->addRewriteCond('%{HTTP_HOST}', '=' . $domain);
        if ($mindWellKnown) {
            $this->addRewriteCond('%{REQUEST_URI}', '!^/.well-known/?.*');
        }
        $this->addRewriteCond('%{REQUEST_URI}', '!^/' . $appFile);
        $this->addRewriteCond('%{REQUEST_FILENAME}', '!-f');
        $this->addRewriteRule('.*', $appFile, ['L']);
        return $this;
    }

    public function addDefaultController(string $controller = 'index.php', bool $mindWellKnown = false): self
    {
        $this->addDirective(new Comment('Default controller'));
        $this->addRewriteCond('%{REQUEST_URI}', '!^/' . $controller);
        if ($mindWellKnown) {
            $this->addRewriteCond('%{REQUEST_URI}', '!^/.well-known/?.*');
        }
        $this->addRewriteCond('%{REQUEST_FILENAME}', '!-f');
        $this->addRewriteCond('%{REQUEST_FILENAME}', '!-d');
        $this->addRewriteRule('^', '/' . $controller, ['L']);
        return $this;
    }
}
