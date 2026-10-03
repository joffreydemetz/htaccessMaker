<?php

namespace Tests\Directive;

use JDZ\HtaccessMaker\Directive\AddHandler;
use Tests\DirectiveTest;

class AddHandlerTest extends DirectiveTest
{
    public function testAddHandlerWithArrayExtensions(): void
    {
        $handler = new AddHandler('cgi-script', ['.php', '.pl', '.py', '.cgi']);

        $this->assertStringContainsString('AddHandler cgi-script .php .pl .py .cgi', $handler->toString());
    }

    public function testAddHandlerEmpty(): void
    {
        $handler = new AddHandler('cgi-script');
        $this->assertEquals('', $handler->toString());
    }
}
