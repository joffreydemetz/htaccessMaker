<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\RewriteEngine;

class RewriteEngineTest extends TestCase
{
    public function testOnByDefault(): void
    {
        $this->assertSame('RewriteEngine On', (new RewriteEngine())->toString());
    }

    #[DataProvider('statuses')]
    public function testOnlyOffSwitchesItOff(string $status, string $expected): void
    {
        $this->assertSame($expected, (new RewriteEngine($status))->toString());
    }

    public static function statuses(): array
    {
        return [
            'On' => ['On', 'RewriteEngine On'],
            'off, any case' => ['oFF', 'RewriteEngine Off'],
            'anything else is On' => ['disabled', 'RewriteEngine On'],
        ];
    }
}
