<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\ExpiresModule;

class ExpiresModuleTest extends TestCase
{
    private const COMMON_RULES = [
        '  ExpiresByType image/jpg "access plus 1 month"',
        '  ExpiresByType image/jpeg "access plus 1 month"',
        '  ExpiresByType image/gif "access plus 1 month"',
        '  ExpiresByType image/png "access plus 1 month"',
        '  ExpiresByType text/css "access plus 1 month"',
        '  ExpiresByType text/javascript "access plus 1 month"',
        '  ExpiresByType application/pdf "access plus 1 month"',
        '  ExpiresByType application/javascript "access plus 1 month"',
        '  ExpiresByType application/x-javascript "access plus 1 month"',
        '  ExpiresByType image/x-icon "access plus 1 year"',
    ];

    // Regression (1.0.9): bare ExpiresActive 500s on servers without mod_expires
    public function testExpiresModuleEmitsIfModuleWrapperByDefault(): void
    {
        $this->assertSame("<IfModule mod_expires.c>\n  ExpiresActive On\n</IfModule>\n\n", (new ExpiresModule())->toString());
    }

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $module = new ExpiresModule();
        $module->process($config);

        $this->assertSame($expected, $module->toString());
    }

    public static function processCases(): array
    {
        $cssRule = '  ExpiresByType text/css "access plus 1 year"';
        $pngRule = '  ExpiresByType image/png "access plus 6 months"';
        $rules = [
            ['mimeType' => 'text/css', 'expiry' => 'access plus 1 year'],
            ['mimeType' => 'image/png', 'expiry' => 'access plus 6 months'],
        ];

        return [
            'defaults: common rules, then a 2-day default' => [['enabled' => true], self::module([
                ...self::COMMON_RULES,
                '  ExpiresDefault "access plus 2 days"',
            ])],
            'defaultExpiry comes first, common rules kept' => [['defaultExpiry' => 'access plus 1 week'], self::module([
                '  ExpiresDefault "access plus 1 week"',
                ...self::COMMON_RULES,
            ])],
            'cacheRules replace the common rules, no default added' => [['cacheRules' => $rules], self::module([
                $cssRule,
                $pngRule,
            ])],
            'defaultExpiry and cacheRules' => [['defaultExpiry' => 'access plus 1 day', 'cacheRules' => $rules], self::module([
                '  ExpiresDefault "access plus 1 day"',
                $cssRule,
                $pngRule,
            ])],
            'common rules off and nothing else: ExpiresActive alone' => [['useCommonRules' => false], self::module([])],
            'empty config adds nothing' => [[], self::module([])],
        ];
    }

    /**
     * @param list<string> $lines indented lines between ExpiresActive On and the closing tag
     */
    private static function module(array $lines): string
    {
        return implode("\n", ['<IfModule mod_expires.c>', '  ExpiresActive On', ...$lines, '</IfModule>', '', '']);
    }
}
