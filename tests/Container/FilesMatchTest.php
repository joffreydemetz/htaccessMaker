<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\FilesMatch;

class FilesMatchTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = FilesMatch::class;

    public function testFilesMatchWithPattern(): void
    {
        $container = new FilesMatch();
        $container->process(['pattern' => '\.(css|js)$']);
        $container->addDirective('Header set Cache-Control "public, max-age=31536000"');

        $this->assertSame(
            "<FilesMatch \"\\.(css|js)$\">\n  Header set Cache-Control \"public, max-age=31536000\"\n</FilesMatch>\n\n",
            $container->toString()
        );
    }

    public function testPatternKeptWhenProcessedAgainWithoutOne(): void
    {
        $container = new FilesMatch();
        $container->process(['pattern' => '\.ico$']);
        $container->process(['enabled' => true]);
        $container->addDirective('Header set Cache-Control "public"');

        $this->assertSame("<FilesMatch \"\\.ico$\">\n  Header set Cache-Control \"public\"\n</FilesMatch>\n\n", $container->toString());
    }

    #[DataProvider('withoutPattern')]
    public function testRendersNothingWithoutAPattern(array $config): void
    {
        $container = new FilesMatch();
        $container->process($config);
        $container->addDirective('Header set Cache-Control "public"');

        $this->assertSame('', $container->toString());
    }

    public static function withoutPattern(): array
    {
        return [
            'enabled, no pattern' => [['enabled' => true]],
            'empty pattern' => [['pattern' => '']],
        ];
    }
}
