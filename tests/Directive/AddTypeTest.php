<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\AddType;

class AddTypeTest extends TestCase
{
    public function testBasic(): void
    {
        $directive = new AddType('image/svg+xml .svg .svgz');

        $this->assertSame('AddType image/svg+xml .svg .svgz', $directive->toString());
    }

    #[DataProvider('blankValues')]
    public function testBlankValueRendersNothing(string $value): void
    {
        $directive = new AddType($value);

        $this->assertSame('', $directive->toString());
    }

    public static function blankValues(): array
    {
        return [
            'empty' => [''],
            'whitespace only' => ['   '],
        ];
    }
}
