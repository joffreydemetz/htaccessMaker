# HtaccessMaker

A PHP library for generating Apache .htaccess files programmatically. Build secure, optimized, and maintainable .htaccess configurations using an object-oriented approach.

I use this library to manage my clients htaccess files based on their needs and requirements. It allows me to quickly generate .htaccess files with the necessary security, performance, and routing rules without manually writing complex Apache directives.

## Features

- **🔒 Security-First**: Security headers (anti-XSS, HSTS, referrer policy), Content Security Policy, URL / SQL / shell injection blocking, basic authentication
- **⚡ Performance Optimized**: Compression, expiry headers, cookie-free static files
- **🏗️ Modular Architecture**: Directives, containers and Apache-module wrappers that nest freely
- **🔧 Flexible Configuration**: Array-based `process()` configuration or the fluent API
- **📝 Type-Safe**: Full PHP type declarations and PHPDoc documentation

## Installation

```bash
composer require jdz/htaccessmaker
```

## Requirements

- PHP 8.2 or higher
- [`jdz/cspmaker`](https://jdz.joffreydemetz.com/cspmaker) ^1.0 — builds the Content Security Policy

## Quick Start

### Basic Usage

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Container\AntiXSS;
use JDZ\HtaccessMaker\Module\DeflateModule;
use JDZ\HtaccessMaker\Module\ExpiresModule;
use JDZ\HtaccessMaker\Directive\ServerSignature;

$htaccess = new HtAccess();

// Basic directives
$htaccess->addDirective(new ServerSignature('Off'));

// Security headers
$antiXSS = new AntiXSS();
$antiXSS->process([
    'frameOptions' => 'DENY',
    'strictTransportSecurity' => 'max-age=31536000; includeSubDomains',
]);
$htaccess->addDirective($antiXSS);

// Compression
$compression = new DeflateModule();
$compression->process([
    'mimeTypes' => ['text/html', 'text/css', 'application/javascript'],
]);
$htaccess->addDirective($compression);

// Caching rules
$expires = new ExpiresModule();
$expires->process([
    'cacheRules' => [
        ['mimeType' => 'text/css', 'expiry' => 'access plus 1 year'],
        ['mimeType' => 'application/javascript', 'expiry' => 'access plus 1 year'],
    ],
]);
$htaccess->addDirective($expires);

// Generate .htaccess content
file_put_contents(__DIR__ . '/public/.htaccess', $htaccess->toString());
```

### How `process()` works

Every container and module is configured with `process(array $config)`. The
config is merged over the class defaults (listed in the
[Configuration Reference](#configuration-reference)).

- A **non-empty** config enables the container. An empty `process()` call adds
  nothing — pass `['enabled' => true]` to apply the defaults alone.
- `NegociationModule`, `RoutingRewrite` and `SecurityRewrite` are the
  exceptions: they always apply their defaults, so `process()` with no argument
  is enough.

### Fluent Interface

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\Directive\ServerSignature;
use JDZ\HtaccessMaker\Module\ForceSecureRewrite;

$https = new ForceSecureRewrite();
$https->process(['excludePaths' => ['/api/webhook']]);

$output = (new HtAccess())
    ->withComments(true)
    ->withApacheCompatibility(true)
    ->addDirective(new Comment('Security Configuration'))
    ->addDirective(new ServerSignature('Off'))
    ->addDirective($https)
    ->toString();

echo $output;
```

### Comments and Apache Compatibility

- `withComments(false)` drops every `Comment` directive and every raw string
  line that starts with `#` — except comments forced with
  `setForceComment(true)`.
- Modules extending `IfModule` print their directives bare by default.
  `withApacheCompatibility(true)` wraps each of them in its
  `<IfModule mod_xxx.c>` block (propagated to nested containers).
  `DeflateModule` and `ExpiresModule` are always wrapped; a single module can be
  wrapped with `->withIgnoreTag(false)`.

## Core Components

### Main Classes (`JDZ\HtaccessMaker\`)

- **`HtAccess`** - Main class for generating .htaccess files
- **`HtPasswd`** - Generate .htpasswd files for basic authentication (APR1-MD5); each entry is preceded by its clear-text password as a comment line, so strip it if the file must not hold it
- **`Container`** - Base class for grouping related directives
- **`IfModule`** - Container wrapped in `<IfModule …>` (see above)
- **`Directive`** - Base class for individual Apache directives
- **`EmptyLine`** - A blank line in the output
- **`Csp`** - *Deprecated* backward-compatible shim over `JDZ\CspMaker\Policy` (keeps `addToGroup()` / `merge($csp, $overwrite)`); use `jdz/cspmaker`'s `CspBuilder` / `Policy` directly

### Containers (`JDZ\HtaccessMaker\Container\`)

- **`AntiXSS`** - X-XSS-Protection, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Strict-Transport-Security
- **`CspContainer`** - Content-Security-Policy header, built with `jdz/cspmaker`
- **`BrowserRender`** - Content-Style-Type / Content-Script-Type headers on PHP and HTML files
- **`UaCompatible`** - X-UA-Compatible header, optionally unset on static files
- **`PreventCookie`** - Cookie-free, long-cached static files (a `FilesMatch`)
- **`MimeTypes`** - `AddType` definitions
- **`ErrorDocuments`** - Custom error pages
- **`FilesMatch`** - `<FilesMatch "pattern">` block
- **`LimitExcept`** - `<LimitExcept METHOD …>` block

### Apache Modules (`JDZ\HtaccessMaker\Module\`)

- **`DeflateModule`** - Gzip compression (`mod_deflate`)
- **`ExpiresModule`** - Cache expiry headers by MIME type (`mod_expires`)
- **`BasicAuthModule`** - HTTP Basic Authentication with allowed paths / user agents (`mod_auth_basic`)
- **`NegociationModule`** - MultiViews and `IndexIgnore` (`mod_negotiation`)
- **`RewriteModule`** - Base URL rewriting (`mod_rewrite`): `addRewriteBase()`, `addRewriteCond()`, `addRewriteRule()`, `addMaintenanceMode()`
- **`ForceSecureRewrite`** - Force HTTPS redirection
- **`RedirectWwwRewrite`** - WWW to non-WWW redirection
- **`MaintenanceRewrite`** - Maintenance mode with IP whitelisting
- **`RoutingRewrite`** - Trailing slash, versioned files, per-domain apps and front controller routing
- **`SecurityRewrite`** - URL attack, SQL / shell injection, HTTP method, user agent and referrer blocking

### Directives (`JDZ\HtaccessMaker\Directive\`)

`AddHandler`, `AddType`, `Comment`, `DirectoryIndex`, `ErrorDocument`,
`ExpiresByType`, `ExpiresDefault`, `FileETag`, `Header` (`withAlways()`,
`setCondition()`), `IndexIgnore`, `Options`, `RewriteBase`, `RewriteCond`,
`RewriteEngine`, `RewriteRule`, `ServerSignature`, and `ValueDirective` (the
base for single-value directives).

## Advanced Usage

### Custom Security Configuration

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Module\SecurityRewrite;

$htaccess = new HtAccess();

$security = new SecurityRewrite();
$security->process([
    'blockUrlAttacks' => true,
    'blockSqlInjection' => true,
    'blockShellInjection' => true,
    'blockMaliciousRequests' => true,
    'blacklistUserAgents' => ['badbot'],
]);

$htaccess->addDirective($security);
```

### Maintenance Mode Setup

```php
<?php

use JDZ\HtaccessMaker\Module\MaintenanceRewrite;

$maintenance = new MaintenanceRewrite();
$maintenance->process([
    'allowedIps' => ['192.168.1.100', '10.0.0.1'],
    'maintenanceFile' => '/maintenance.html',
    'defaultState' => false, // Default state: off
]);

// Generates both ON and OFF sections - just uncomment the needed one
```

### Routing

```php
<?php

use JDZ\HtaccessMaker\Module\RoutingRewrite;

$routing = new RoutingRewrite();
$routing->process([
    'baseUrl' => '/',
    'defaultController' => 'index.php',
    'domainApps' => [
        ['domain' => 'api.example.com', 'file' => 'api.php'],
    ],
    'rules' => [
        ['from' => '^old-page/?$', 'to' => '/new-page/'], // flags default to R=301,L
    ],
]);
```

### Content Security Policy

```php
<?php

use JDZ\HtaccessMaker\Container\CspContainer;

$csp = new CspContainer();
$csp->process([
    'csp' => [
        'script' => ['self', 'https://cdn.example.com'],
        'frame-ancestors' => ['none'],
    ],
    'integrations' => [
        'googleFonts',
        ['matomo' => ['host' => 'stats.example.com']],
    ],
]);
```

Each `csp` entry replaces that directive's default; short directive names
(`script`, `img`, …) and source keywords (`self`, `none`, `data`) are normalized
by `jdz/cspmaker`. `integrations` applies its named recipes (`matomo`,
`googleFonts`, `googleAnalytics`, `googleTagManager`, `youtube`, `recaptcha`,
`stripe`).

## Configuration Reference

Defaults in parentheses.

| Class | `process()` keys |
|---|---|
| `AntiXSS` | `xssProtection` (`1; mode=block`), `frameOptions` (`SAMEORIGIN`), `contentTypeOptions` (`nosniff`), `refererPolicy` (`strict-origin-when-cross-origin`), `strictTransportSecurity` (empty = not sent) |
| `CspContainer` | `csp` (directive => sources), `integrations`, `useXContentSecurityPolicy` (`false`); default policy: `default`/`script`/`style`/`font`/`connect`/`child`/`media`/`object`/`manifest` `'self'`, `img` `'self' data:` |
| `UaCompatible` | `browsers` (`IE=Edge`), `unsetOnStaticFiles` (`true`), `staticFilesExtensions` |
| `PreventCookie` | `maxAge` (`31536000`), `removeCookies`, `setCacheControl`, `setVaryHeaders`, `setConnectionHeaders`, `disableETags` (all `true`); matches `css js png jpg jpeg gif ico svg woff woff2 ttf eot` files |
| `MimeTypes` | `mimeTypes` (`['type' => '.ext']` or `[['type' => …, 'extensions' => […]]]`; empty = common web types), `includeCommonWeb`, `includeImages`, `includeDocuments` |
| `ErrorDocuments` | `errorDocuments` (`[404 => '/404.html']` or `[['code' => 404, 'url' => …]]`; default 404 → `/error-404.html`), `commonErrorPages` (base URL for 400–503 pages), `customErrors` |
| `FilesMatch` | `pattern` |
| `LimitExcept` | `authMethods` |
| `DeflateModule` | `mimeTypes` (`text/html`, `text/css`, `application/javascript`, `application/json`), `browserCompatibility` (`true`), `fileExclusions` (`[]`), `varyHeader` (`true`) |
| `ExpiresModule` | `cacheRules` (`[['mimeType' => …, 'expiry' => 'access plus 1 month']]`), `defaultExpiry`, `useCommonRules` (`true` — used when `cacheRules` is empty, with a 2-day default) |
| `BasicAuthModule` | `authName` (`Protected Area`), `authUserFile`, `allowedPaths`, `allowedUserAgents`, `passwordComment`, `defaultPaths` (`true` — favicon manifest files) |
| `NegociationModule` | `multiViews` (`false`), `indexIgnore` (`*`), `customOptions` |
| `ForceSecureRewrite` | `excludePaths` |
| `RedirectWwwRewrite` | — (`['enabled' => true]`) |
| `MaintenanceRewrite` | `allowedIps`, `maintenanceFile` (`/maintenance.html`), `defaultState` (`false`) |
| `RoutingRewrite` | `baseUrl` (`/`), `checkTrailingSlash` (`true`), `versionedFiles` (`true`), `domainApps` (`[['domain' => …, 'file' => …]]`), `rules` (raw strings or `['from', 'to', 'flags']`), `defaultController` (`index.php`) |
| `SecurityRewrite` | `blockUrlAttacks`, `blockSqlInjection`, `blockShellInjection`, `blockMaliciousRequests` (all `true`), `blacklistHttpMethods` (`HEAD TRACE TRACK OPTIONS PUT DELETE`), `blacklistUserAgents`, `blacklistReferrers`, `blockAction` (`/index.php`), `blockFlags` (`R=403,L`) |
| `BrowserRender` | — (`['enabled' => true]`) |

## API Reference

### HtAccess Methods

```php
$htaccess->withComments(bool $showComments = true): self
$htaccess->withApacheCompatibility(bool $ensure = true): self
$htaccess->addDirective(Directive|Container|string $directive): self
$htaccess->toString(): string

// Render a single directive or container on its own
HtAccess::stringifyDirective(Directive|Container|string $directive, bool $showComments = true, bool $ensureApacheCompatibility = true, int $indent = 0): string
```

### Container Methods

```php
// All containers support
$container->process(array $config = []): void
$container->addDirective(Directive|Container|string $directive): self
$container->ensureApacheCompatibility(): self
$container->toString(bool $showComments = true, int $indent = 0): string
```

### HtPasswd Methods

```php
$htpasswd->addUser(string $name, string $password): self
$htpasswd->toString(): string
```

## Project Structure

```
src/
├── HtAccess.php              # Main .htaccess generator
├── HtPasswd.php              # .htpasswd generator
├── Container.php             # Base container class
├── IfModule.php              # <IfModule> container
├── Directive.php             # Base directive class
├── EmptyLine.php
├── Csp.php                   # Deprecated shim over jdz/cspmaker
├── Container/                # Header, CSP, MIME, error, FilesMatch… containers
│   ├── AntiXSS.php
│   ├── CspContainer.php
│   └── ...
├── Module/                   # Apache module wrappers (IfModule)
│   ├── DeflateModule.php
│   ├── ExpiresModule.php
│   ├── RewriteModule.php
│   └── ...
└── Directive/                # Apache directive classes
    ├── Header.php
    ├── RewriteRule.php
    └── ...
```

## Examples

See the `examples/` directory for a complete, configuration-driven setup (it
uses `symfony/yaml` and `jdz/data`, both dev dependencies):

- **`example.php`** - Generates a website, an API and a password-protected dev .htaccess into `examples/exports/`
- **`base.class.php`** - `BaseHtAccess`, which maps the YAML keys onto the containers and modules
- **`myhtaccess.php`** - The YAML config loader and the `createHtAccessFromConfig()` helper
- **`config/`** - YAML configuration layers (`core`, `web`, `front`, `website`, `api`, `dev`)

## Changelog

- **1.1.1** - `jdz/cspmaker` is resolved from Packagist (no local path repository).
- **1.1.0** - CSP building moved to `jdz/cspmaker` (new dependency): `CspContainer` builds through `CspBuilder` and accepts `integrations`; `Csp` becomes a deprecated shim over `JDZ\CspMaker\Policy`.
- **1.0.9** - Fixed malformed `Header` / `ExpiresModule` output that made Apache answer 500: `Header::withVary()` is a no-op, `setCondition()` emits `env=…`, `PreventCookie` appends real `Vary` headers, `ExpiresModule` is always wrapped in its `<IfModule>`.
- **1.0.8** - `FilesMatch` / `LimitExcept` show comments by default.

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## Author

Joffrey Demetz <joffrey.demetz@gmail.com>

---

**Built with ❤️ for secure and maintainable Apache configurations**
