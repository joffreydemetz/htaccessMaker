<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\NegociationModule;

class NegociationModuleTest extends TestCase
{
    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $module = new NegociationModule();
        $module->process($config);

        $this->assertSame($expected, $module->toString());
    }

    public static function processCases(): array
    {
        return [
            'defaults, even without a config: MultiViews off, IndexIgnore *' => [[], "Options -MultiViews\nIndexIgnore *\n\n"],
            'MultiViews on, custom options, no IndexIgnore' => [
                ['multiViews' => true, 'customOptions' => ['-Indexes', '+FollowSymLinks'], 'indexIgnore' => ''],
                "Options +MultiViews\nOptions -Indexes +FollowSymLinks\n\n",
            ],
            'custom IndexIgnore pattern' => [['indexIgnore' => '*.map'], "Options -MultiViews\nIndexIgnore *.map\n\n"],
        ];
    }

    public function testWrappedWithApacheCompatibility(): void
    {
        $module = new NegociationModule();
        $module->process();
        $module->ensureApacheCompatibility();

        $this->assertSame("<IfModule mod_negotiation.c>\n  Options -MultiViews\n  IndexIgnore *\n</IfModule>\n\n", $module->toString());
    }
}
