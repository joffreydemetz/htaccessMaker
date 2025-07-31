# HtaccessMaker

A PHP library for generating Apache .htaccess files programmatically. Build secure, optimized, and maintainable .htaccess configurations using an object-oriented approach.

I use this library to manage my clients htaccess files based on their needs and requirements. It allows me to quickly generate .htaccess files with the necessary security, performance, and routing rules without manually writing complex Apache directives.

## Features

- **🔒 Security-First**: Built-in security containers for XSS protection, CSRF prevention, and attack blocking
- **⚡ Performance Optimized**: Compression, caching, and static file optimization
- **🏗️ Modular Architecture**: Reusable containers and directives for clean code organization
- **🔧 Flexible Configuration**: YAML/array-based configuration or fluent API
- **🧪 Fully Tested**: Comprehensive unit test suite with 1000+ tests
- **📝 Type-Safe**: Full PHP type declarations and PHPDoc documentation

## Installation

```bash
composer require jdz/htaccessmaker
```

## Quick Start

### Basic Usage

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Container\AntiXSS;
use JDZ\HtaccessMaker\Container\DeflateModule;
use JDZ\HtaccessMaker\Container\ExpiresModule;
use JDZ\HtaccessMaker\Directive\ServerSignature;

$htaccess = new HtAccess();

// Add security headers
$antiXSS = new AntiXSS();
$antiXSS->process([
    'xssProtection' => '1; mode=block',
    'frameOptions' => 'DENY',
    'contentTypeOptions' => 'nosniff'
]);
$htaccess->addDirective($antiXSS);

// Add compression
$compression = new DeflateModule();
$compression->process([
    'mimeTypes' => ['text/html', 'text/css', 'application/javascript']
]);
$htaccess->addDirective($compression);

// Add caching rules  
$expires = new ExpiresModule();
$expires->process([
    'cacheRules' => [
        ['mimeType' => 'text/css', 'expiry' => '1 year'],
        ['mimeType' => 'application/javascript', 'expiry' => '1 year']
    ]
]);
$htaccess->addDirective($expires);

// Add basic directives
$htaccess->addDirective(new ServerSignature('Off'));

// Generate .htaccess content
echo $htaccess->toString();
```

### Fluent Interface

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Container\AntiXSS;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\Directive\ServerSignature;

$antiXSS = new AntiXSS();
$antiXSS->process([
    'xssProtection' => '1; mode=block',
    'frameOptions' => 'SAMEORIGIN',
    'strictTransportSecurity' => 'max-age=31536000; includeSubDomains'
]);

$output = (new HtAccess())
    ->withComments(true)
    ->ensureApacheCompatibility(true)
    ->addDirective(new Comment('Security Configuration'))
    ->addDirective(new ServerSignature('Off'))
    ->addDirective($antiXSS)
    ->toString();

echo $output;
```

## Core Components

### Main Classes

- **`HtAccess`** - Main class for generating .htaccess files
- **`HtPasswd`** - Generate .htpasswd files for basic authentication
- **`Container`** - Base class for grouping related directives
- **`Directive`** - Base class for individual Apache directives
- **`Csp`** - Content Security Policy builder

### Security Containers

- **`AntiXSS`** - XSS protection, frame options, content type options
- **`BasicAuthModule`** - HTTP Basic Authentication setup
- **`SecurityRewrite`** - URL attack prevention and malicious content blocking

### Performance Containers

- **`DeflateModule`** - Gzip compression configuration
- **`ExpiresModule`** - Cache expiry headers by MIME type
- **`BrowserRender`** - Browser compatibility and rendering optimization
- **`PreventCookie`** - Prevent cookies on static files

### URL Management

- **`RewriteModule`** - Base URL rewriting functionality
- **`ForceSecureRewrite`** - Force HTTPS redirection
- **`RedirectWwwRewrite`** - WWW to non-WWW redirection  
- **`MaintenanceRewrite`** - Maintenance mode with IP whitelisting
- **`RoutingRewrite`** - Application routing rules

### Content Management

- **`MimeTypes`** - MIME type definitions
- **`ErrorDocuments`** - Custom error pages
- **`CspContainer`** - Content Security Policy headers
- **`UaCompatible`** - Browser compatibility headers

## Advanced Usage

### Custom Security Configuration

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Container\SecurityRewrite;

$htaccess = new HtAccess();

// Add comprehensive security rules
$security = new SecurityRewrite();
$security->process([
    'blockShellUploaders' => true,
    'blockSqlInjection' => true,
    'blockUrlAttacks' => true,
    'blockMaliciousUserAgents' => true
]);

$htaccess->addDirective($security);
```

### Maintenance Mode Setup

```php
<?php

use JDZ\HtaccessMaker\Container\MaintenanceRewrite;

$maintenance = new MaintenanceRewrite([
    '192.168.1.100',  // Allowed IPs
    '10.0.0.1'
], '/maintenance.html', false); // Default state: off

// Generates both ON and OFF sections - just uncomment the needed one
```

### Multiple Rewrite Modules

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Container\ForceSecureRewrite;
use JDZ\HtaccessMaker\Container\SecurityRewrite;
use JDZ\HtaccessMaker\Container\RoutingRewrite;

$htaccess = new HtAccess();

// Force HTTPS (excluding certain paths)
$httpsRedirect = new ForceSecureRewrite(['/api/webhook']);
$htaccess->addDirective($httpsRedirect);

// Security rules
$security = new SecurityRewrite();
$htaccess->addDirective($security);

// Application routing
$routing = new RoutingRewrite();
$routing->process([
    'rewriteBase' => '/',
    'indexFile' => 'index.php'
]);
$htaccess->addDirective($routing);
```

### Comments and Apache Compatibility

```php
<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Container\DeflateModule;
use JDZ\HtaccessMaker\Directive\Comment;

$htaccess = new HtAccess();

// Control comments and Apache compatibility
$htaccess
    ->withComments(true)           // Enable/disable comments
    ->ensureApacheCompatibility(true); // Wrap containers in IfModule

$htaccess->addDirective(new Comment('Performance optimizations'));

// DeflateModule always renders regardless of ensureApacheCompatibility setting
$compression = new DeflateModule();
$compression->process(['mimeTypes' => ['text/html', 'text/css']]);
$htaccess->addDirective($compression);

echo $htaccess->toString();
```

## Configuration Reference

### AntiXSS Configuration

```php
$antiXSS = new AntiXSS();
$antiXSS->process([
    'xssProtection' => '1; mode=block',
    'frameOptions' => 'DENY|SAMEORIGIN|ALLOW-FROM uri',
    'contentTypeOptions' => 'nosniff',
    'referrerPolicy' => 'strict-origin-when-cross-origin',
    'strictTransportSecurity' => 'max-age=31536000; includeSubDomains'
]);
```

### Compression Configuration

```php
$compression = new DeflateModule();
$compression->process([
    'mimeTypes' => [
        'text/html',
        'text/css', 
        'application/javascript',
        'application/json',
        'image/svg+xml'
    ]
]);
```

### Cache Expiry Configuration

```php
$expires = new ExpiresModule();
$expires->process([
    'cacheRules' => [
        ['mimeType' => 'text/css', 'expiry' => '1 year'],
        ['mimeType' => 'application/javascript', 'expiry' => '1 year'],
        ['mimeType' => 'image/png', 'expiry' => '1 month'],
        ['mimeType' => 'image/jpeg', 'expiry' => '1 month']
    ]
]);
```

### Basic Authentication Configuration

```php
$basicAuth = new BasicAuthModule();
$basicAuth->process([
    'realm' => 'Restricted Area',
    'userFile' => '/path/to/.htpasswd',
    'require' => 'valid-user',
    'allowedIps' => ['192.168.1.0/24']
]);
```

## Testing

Work in progress 

Run the comprehensive test suite:

```bash
# Run all tests
./vendor/bin/phpunit

# Run all tests with testdox format
./vendor/bin/phpunit --testdox

# Run specific test suites
./vendor/bin/phpunit --testsuite=Core
./vendor/bin/phpunit --testsuite=Container
./vendor/bin/phpunit --testsuite=Directive
./vendor/bin/phpunit --testsuite=Examples

# Using composer scripts
composer test
composer test:core
```

## API Reference

### HtAccess Methods

```php
// Fluent interface methods
$htaccess->withComments(bool $showComments = true): self
$htaccess->ensureApacheCompatibility(bool $ensure = true): self
$htaccess->addDirective(Directive|Container|string $directive): self

// Utility methods
$htaccess->toString(): string
$htaccess->directiveToString(Directive|Container|string $directive, bool $showComments = true, int $indent = 0): string
```

### Container Methods

```php
// All containers support
$container->process(array $config=[]): void
$container->addDirective(Directive|Container|string $directive): self
$container->toString(bool $showComments = true, int $indent = 0): string
```

## Project Structure

```
src/
├── Container.php             # Base container class
├── Directive.php             # Base directive class  
├── HtAccess.php              # Main .htaccess generator
├── HtPasswd.php              # .htpasswd generator
├── Csp.php                   # Content Security Policy builder
├── Container/                # Specialized containers
│   ├── AntiXSS.php
│   ├── BasicAuthModule.php
│   ├── DeflateModule.php
│   ├── ExpiresModule.php
│   ├── RewriteModule.php
│   └── ...
└── Directive/                # Apache directive classes
    ├── Header.php
    ├── RewriteRule.php
    ├── Options.php
    ├── AddHandler.php
    └── ...

tests/                        # Comprehensive test suite
├── Container/
├── Directive/
├── Examples/
└── ...
```

## Examples

See the `examples/` directory for complete working examples:

- **`example.php`** - Comprehensive .htaccess generation
- **`base.class.php`** - Configuration-driven approach
- **`config/`** - YAML configuration examples

## Requirements

- PHP 8.1 or higher
- Composer

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
