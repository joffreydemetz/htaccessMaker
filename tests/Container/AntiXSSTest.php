<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\Container\AntiXSS;

class AntiXSSTest extends ContainerTest
{
    protected string $containerClass = AntiXSS::class;

    public function testProcessWithCustomConfig(): void
    {
        $container = new AntiXSS();
        $container->process([
            'xssProtection' => '1; mode=block',
            'frameOptions' => 'DENY',
            'contentTypeOptions' => 'nosniff',
            'refererPolicy' => 'no-referrer',
            'strictTransportSecurity' => 'max-age=31536000; includeSubDomains'
        ]);

        $output = $container->toString(true);

        $this->assertStringContainsString('X-XSS-Protection', $output);
        $this->assertStringContainsString('1; mode=block', $output);
        $this->assertStringContainsString('X-Frame-Options', $output);
        $this->assertStringContainsString('DENY', $output);
        $this->assertStringContainsString('X-Content-Type-Options', $output);
        $this->assertStringContainsString('nosniff', $output);
        $this->assertStringContainsString('Referrer-Policy', $output);
        $this->assertStringContainsString('no-referrer', $output);
        $this->assertStringContainsString('Strict-Transport-Security', $output);
        $this->assertStringContainsString('max-age=31536000', $output);
    }

    public function testProcessWithDisabledHeaders(): void
    {
        $container = new AntiXSS();
        $container->process([
            'enabled' => true,
            'xssProtection' => false,
            'frameOptions' => '',
            'contentTypeOptions' => null,
            'refererPolicy' => false,
            'strictTransportSecurity' => ''
        ]);

        $output = $container->toString(true);

        // Should only contain the comment since all headers are disabled
        $this->assertStringNotContainsString('X-XSS-Protection', $output);
        $this->assertStringNotContainsString('X-Frame-Options', $output);
        $this->assertStringNotContainsString('X-Content-Type-Options', $output);
        $this->assertStringNotContainsString('Referrer-Policy', $output);
        $this->assertStringNotContainsString('Strict-Transport-Security', $output);
    }
}
