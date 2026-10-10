<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\BrowserRender;

class BrowserRenderTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = BrowserRender::class;

    public function testProcess(): void
    {
        $container = new BrowserRender();
        $container->process(['enabled' => true]);

        // the nested FilesMatch keeps its own trailing blank line
        $this->assertSame(implode("\n", [
            '<FilesMatch ".(php|html)$">',
            '  Header set Content-Style-Type "text/css"',
            '  Header set Content-Script-Type "text/javascript"',
            '</FilesMatch>',
            '',
            '',
            '',
            '',
        ]), $container->toString());
    }
}
