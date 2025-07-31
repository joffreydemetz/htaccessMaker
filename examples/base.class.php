<?php

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Container\LimitExcept;
use JDZ\HtaccessMaker\Container\CspContainer;
use JDZ\HtaccessMaker\Container\UaCompatible;
use JDZ\HtaccessMaker\Container\PreventCookie;
use JDZ\HtaccessMaker\Container\BrowserRender;
use JDZ\HtaccessMaker\Container\AntiXSS;
use JDZ\HtaccessMaker\Container\MimeTypes;
use JDZ\HtaccessMaker\Container\ErrorDocuments;
use JDZ\HtaccessMaker\Directive\Header;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\Directive\ServerSignature;
use JDZ\HtaccessMaker\Directive\DirectoryIndex;
use JDZ\HtaccessMaker\Directive\Options;
use JDZ\HtaccessMaker\Module\ForceSecureRewrite;
use JDZ\HtaccessMaker\Module\SecurityRewrite;
use JDZ\HtaccessMaker\Module\MaintenanceRewrite;
use JDZ\HtaccessMaker\Module\RoutingRewrite;
use JDZ\HtaccessMaker\Module\RedirectWwwRewrite;
use JDZ\HtaccessMaker\Module\DeflateModule;
use JDZ\HtaccessMaker\Module\NegociationModule;
use JDZ\HtaccessMaker\Module\BasicAuthModule;
use JDZ\HtaccessMaker\Module\ExpiresModule;

class BaseHtAccess extends MyHtaccess
{
    public function process(): void
    {
        parent::process();

        $this->withComments($this->config->getBool('showComments', $this->showComments));
        $this->withApacheCompatibility($this->config->getBool('ensureApacheCompatibility', $this->ensureApacheCompatibility));

        if (false === $this->config->getBool('serverSignature')) {
            $this->addDirective(new ServerSignature('Off'));
        }

        if ($mimeTypes = $this->config->getArray('mimeTypes')) {
            $container = new MimeTypes();
            $container->process([
                'mimeTypes' => $mimeTypes,
            ]);
            $this->addDirective($container);
        }

        if ($errorDocuments = $this->config->getArray('errorDocuments')) {
            $container = new ErrorDocuments();
            $container->process([
                'errorDocuments' => $errorDocuments
            ]);
            $this->addDirective($container);
        }

        if ($directoryIndex = $this->config->get('directoryIndex')) {
            $this->addDirective(new DirectoryIndex($directoryIndex));
        }

        if (true === $this->config->getBool('negociation')) {
            $this->addDirective(new Comment('Negociation'));

            $negociationModule = new NegociationModule();
            $negociationModule->process([
                'multiViews' => $this->config->getBool('negMultiviews'),
                'indexIgnore' => $this->config->get('negIndexIgnorePattern', '*'),
                'customOptions' => $this->config->getArray('negCustomOptions'),
            ]);

            $this->addDirective($negociationModule);
        }

        if (true === $this->config->getBool('options')) {
            $this->addDirective(new Options($this->config->getArray('options')));
        }

        if ($authMethods = $this->config->getArray('authMethods')) {
            $limitExcept = new LimitExcept();
            $limitExcept->process(['authMethods' => $authMethods]);
            $limitExcept->addDirective('Deny from all');
            $this->addDirective($limitExcept);
        }

        $container = new Container();
        $container->addDirective(new Header('X-Callisto-Hi', 'Callisto Framework', 'add'));
        $container->addDirective(new Header('X-Powered-By', null, 'unset'));
        $this->addDirective($container);

        // Anti-XSS
        if ($this->config->getBool('antiXss')) {
            $this->addDirective(new Comment('Anti XSS', '######'));

            $container = new AntiXSS();
            $container->process([
                'xssProtection' => $this->config->get('xssProtection'),
                'frameOptions' => $this->config->get('frameOptions'),
                'contentTypeOptions' => $this->config->get('contentTypeOptions'),
                'strictTransportSecurity' => $this->config->get('strictTransportSecurity'),
                'refererPolicy' => $this->config->get('refererPolicy'),
            ]);
            $this->addDirective($container);
        }

        // Browser rendering
        if (true === $this->config->getBool('browserRender')) {
            // $this->addDirective(new Comment('Browser Rendering', '######'));

            if (true === $this->config->getBool('uaCompatible')) {
                $this->addDirective(new Comment('UA Compatible'));

                $container = new UaCompatible();
                $container->process([
                    'browsers' => $this->config->get('uaCompatibleBrowsers'),
                    'unsetOnStaticFiles' => $this->config->getBool('unsetOnStaticFiles'),
                    'staticFilesExtensions' => $this->config->getArray('staticFilesExtensions'),
                ]);
                $this->addDirective($container);
            }

            $container = new BrowserRender();
            $container->process(['enabled' => true]);
            $this->addDirective($container);
        }

        // Security policy (CSP) from config
        if ($this->config->getBool('securityPolicy')) {
            $this->addDirective(new Comment('Content Security Policy (CSP)', '#######'));

            $container = new CspContainer();
            $container->process([
                'enabled' => true,
                'useXContentSecurityPolicy' => $this->config->getBool('useXContentSecurityPolicy'),
                'csp' => $this->config->getArray('csp'),
            ]);
            $this->addDirective($container);
        }

        // Web caching configuration
        if ($this->config->getBool('webCaching')) {
            $this->addDirective(new Comment('Cache', '######'));

            if ($this->config->getBool('browserRender')) {
                $this->addDirective(new Comment('Prevent Cookies on Static Files'));

                $container = new PreventCookie();
                $container->process([
                    'maxAge' => $this->config->get('preventCookieMaxAge'),
                    'removeCookies' => $this->config->getBool('preventCookieRemoveCookies'),
                    'setCacheControl' => $this->config->getBool('preventCookieSetCacheControl'),
                    'setConnectionHeaders' => $this->config->getBool('preventCookieSetConnectionHeaders'),
                    'disableETags' => $this->config->getBool('preventCookieDisableETags'),
                    'setVaryHeaders' => $this->config->getBool('preventCookieSetVaryHeaders'),
                ]);
                $this->addDirective($container);
            }

            $container = new ExpiresModule();
            $container->process([
                'cacheRules' => $this->config->getArray('cacheRules'),
                'defaultExpiry' => $this->config->get('defaultCacheExpiry'),
                'useCommonRules' => empty($this->config->getArray('cacheRules')),
            ]);
            $this->addDirective($container);
        }

        // Web compression
        if ($this->config->getBool('webCompression')) {
            $container = new DeflateModule();
            $container->process([
                'mimeTypes' => $this->config->getArray('wcMimeTypes'),
                'browserCompatibility' => $this->config->getBool('wcBrowserCompatibility'),
                'fileExclusions' => $this->config->getArray('wcFileExclusions'),
                'varyHeader' => $this->config->getBool('wcVaryHeader'),
            ]);

            $this->addDirective($container);
        }

        // Server authorization
        if (true === $this->config->getBool('serverAuthorization')) {
            $container = new BasicAuthModule();
            $container->process([
                'authName' => $this->config->get('protectedAuthName'),
                'authUserFile' => $this->config->get('htPasswordDir'),
                'allowedPaths' => $this->config->getArray('allowedPaths'),
                'allowedUserAgents' => $this->config->getArray('allowedUserBots'),
                'passwordComment' => $this->config->get('htPasswordUser') . ':' . $this->config->get('htPasswordClear'),
                'defaultPaths' => $this->config->getBool('protectedDefaultPaths'),
            ]);
            $this->addDirective($container);
        }

        $this->addDirective(new Comment('urlRewriting', '#######'));

        // Maintenance mode
        if (true === $this->config->getBool('redirectMaintenance')) {
            $module = new MaintenanceRewrite();
            $module->process([
                'enabled' => true,
                'maintenanceFile' => $this->config->get('maintenanceFile', '/maintenance.html'),
                'allowedIps' => $this->config->getArray('allowedIps'),
            ]);
            $this->addDirective($module);
        }

        // Security blocking
        $this->addDirective(new Comment('Security blocking'));

        $securityModule = new SecurityRewrite();
        $securityModule->process([
            'enabled' => true,
            'blockUrlAttacks' => $this->config->getBool('blockUrlAttacks'),
            'blockSqlInjection' => $this->config->getBool('blockSqlInjection'),
            'blockShellInjection' => $this->config->getBool('blockShellInjection'),
            'blockMaliciousRequests' => $this->config->getBool('blockMaliciousRequests'),
            'blacklistReferrers' => $this->config->getArray('blacklistReferrers'),
            'blacklistUserAgents' => $this->config->getArray('blacklistUserAgents'),
            'blacklistHttpMethods' => $this->config->getArray('blacklistHttpMethods'),
            'blockAction' => $this->config->get('blockAction'),
            'blockFlags' => $this->config->get('blockFlags'),
        ]);
        $this->addDirective($securityModule);

        // SSL redirect
        if (true === $this->config->getBool('forceSsl')) {
            $container = new ForceSecureRewrite();
            $container->process([
                'enabled' => true,
                'excludePaths' => $this->config->getArray('forceSslExcludePaths'),
            ]);
            $this->addDirective($container);
        }

        // WWW redirect
        if (true === $this->config->getBool('redirectWww')) {
            $wwwModule = new RedirectWwwRewrite();
            $wwwModule->process([
                'enabled' => true,
            ]);
            $this->addDirective($wwwModule);
        }

        // Routing
        $this->addDirective(new Comment('Routing', '#######'));

        $routingModule = new RoutingRewrite();
        $routingModule->process([
            'enabled' => true,
            'baseUrl' => $this->config->get('rewriteBase', '/'),
            'checkTrailingSlash' => $this->config->getBool('checkTrailingSlash'),
            'versionedFiles' => $this->config->getBool('versionedFiles'),
            'domainApps' => $this->config->getArray('domainApps'),
            'rules' => $this->config->getArray('rules'),
            'defaultController' => 'index.php',
        ]);
        $this->addDirective($routingModule);
    }
}
