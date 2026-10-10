<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Module\RedirectWwwRewrite;

class RedirectWwwRewriteTest extends BaseContainerTest
{
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = RedirectWwwRewrite::class;

    public function testRedirectWwwRewrite(): void
    {
        $container = new RedirectWwwRewrite();
        $container->process([
            'enabled' => true,
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('Redirect www to non-www', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_HOST} ^www\.(.*)$', $output);
        $this->assertStringContainsString('https://%1/$1', $output);
        $this->assertStringContainsString('R=301', $output);
    }
}
