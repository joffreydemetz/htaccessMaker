<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Directive\Comment;

/**
 * Basic Authentication Module
 * 
 * This module provides directives for setting up basic authentication
 * in an .htaccess file, allowing for protected areas with user credentials.
 * 
 * It supports custom paths that can be accessed without authentication,
 * allowing for more flexible access control.
 * 
 * It also allows for specific user agents to bypass authentication,
 * making it easier to integrate with various clients and services.
 * 
 * It includes options for setting the authentication realm,
 * allowing for more granular control over access permissions.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class BasicAuthModule extends IfModule
{
    protected array $defaults = [
        'authName' => 'Protected Area',
        'authUserFile' => '/path/to/.htpasswd',
        'allowedPaths' => [],
        'allowedUserAgents' => [],
        'passwordComment' => null,
        'defaultPaths' => true,
    ];

    public function __construct()
    {
        parent::__construct('mod_auth_basic.c');
    }

    /**
     * Process configuration to generate basic authentication directives
     */
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        $this->addDirective(new Comment('Basic Authentication'));

        // Allow default specific paths (like favicon files) if enabled
        if ($config['defaultPaths']) {
            $this->addDefaultPaths();
        }

        // Allow additional paths if provided
        if ($config['allowedPaths']) {
            $this->addAllowedPaths($config['allowedPaths']);
        }

        // Allow specific user agents (like social bots)
        if ($config['allowedUserAgents']) {
            $this->addAllowedUserAgents($config['allowedUserAgents']);
        }

        // Basic authentication setup
        $this->setBasicAuth($config['authName'], $config['authUserFile']);

        // Add password comment if provided
        if ($config['passwordComment']) {
            $this->showPasswordComment($config['passwordComment']);
        }
    }

    /**
     * Configure basic authentication settings
     */
    public function setBasicAuth(string $authName, string $authUserFile): self
    {
        $this->addDirective('AuthName "' . $authName . '"');
        $this->addDirective('AuthType Basic');
        $this->addDirective('AuthUserFile ' . $authUserFile);
        $this->addDirective('Require valid-user');
        $this->addDirective('Order deny,allow');
        $this->addDirective('Deny from all');
        $this->addDirective('Allow from env=ForceAllow');
        $this->addDirective('Satisfy Any');
        return $this;
    }

    /**
     * Add password comment that will always be shown (force comment)
     */
    public function showPasswordComment(string $passwordComment): self
    {
        $password = new Comment($passwordComment);
        $password->setForceComment(true);
        $this->addDirective($password);
        return $this;
    }

    public function addAllowPath(string $path): self
    {
        $this->addDirective('SetEnvIf Request_URI "' . $path . '" ForceAllow');
        return $this;
    }

    public function addAllowUserAgent(string $userAgent): self
    {
        $this->addDirective('SetEnvIf User-Agent "' . $userAgent . '" ForceAllow');
        return $this;
    }

    public function addDefaultPaths(): self
    {
        $this->addDirective('SetEnvIf Request_URI "favicon/manifest\.json$" ForceAllow');
        $this->addDirective('SetEnvIf Request_URI "favicon/browserconfig\.xml$" ForceAllow');
        return $this;
    }

    public function addAllowedPaths(array $paths): self
    {
        foreach ($paths as $path) {
            $this->addAllowPath($path);
        }
        return $this;
    }

    public function addAllowedUserAgents(array $userAgents): self
    {
        foreach ($userAgents as $userAgent) {
            $this->addAllowUserAgent($userAgent);
        }
        return $this;
    }
}
