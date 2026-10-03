<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\AddType;

class AddTypeTest extends DirectiveTest
{
    public function testBasic(): void
    {
        $directive = new AddType('text/plain .txt');

        $output = $directive->toString(true);

        $this->assertStringContainsString('AddType text/plain .txt', $output);
    }

    public function testMultipleExtensions(): void
    {
        $directive = new AddType('image/svg+xml .svg .svgz');

        $output = $directive->toString(true);

        $this->assertStringContainsString('AddType image/svg+xml .svg .svgz', $output);
    }

    public function testAddTypeEmpty(): void
    {
        $handler = new AddType('');
        $this->assertEquals('', $handler->toString());
    }
}
