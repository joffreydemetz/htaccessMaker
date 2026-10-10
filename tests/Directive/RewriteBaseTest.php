<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\RewriteBase;

class RewriteBaseTest extends TestCase
{
    #[DataProvider('paths')]
    public function testToString(string $urlPath, string $expected): void
    {
        $directive = new RewriteBase($urlPath);

        $this->assertSame($expected, $directive->toString());
    }

    public static function paths(): array
    {
        return [
            'root' => ['/', 'RewriteBase /'],
            'subdirectory with trailing slash' => ['/app/', 'RewriteBase /app/'],
            'trailing slash added' => ['/api', 'RewriteBase /api/'],
            'empty becomes root' => ['', 'RewriteBase /'],
        ];
    }
}
