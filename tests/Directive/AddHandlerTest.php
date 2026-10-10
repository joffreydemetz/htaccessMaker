<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\AddHandler;

class AddHandlerTest extends TestCase
{
    public function testAddHandlerWithArrayExtensions(): void
    {
        $handler = new AddHandler('cgi-script', ['.php', '.pl', '.py', '.cgi']);

        $this->assertSame('AddHandler cgi-script .php .pl .py .cgi', $handler->toString());
    }

    public function testAddHandlerWithoutExtensionsRendersNothing(): void
    {
        $handler = new AddHandler('cgi-script');

        $this->assertSame('', $handler->toString());
    }
}
