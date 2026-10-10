<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\Options;

class OptionsTest extends TestCase
{
    /**
     * @param string|list<string> $options
     */
    #[DataProvider('optionsCases')]
    public function testToString(string|array $options, string $expected): void
    {
        $directive = new Options($options);

        $this->assertSame($expected, $directive->toString());
    }

    public static function optionsCases(): array
    {
        return [
            'one option' => ['+Indexes', 'Options +Indexes'],
            'space separated string' => ['+Indexes +FollowSymLinks -MultiViews', 'Options +Indexes +FollowSymLinks -MultiViews'],
            'array, duplicates dropped' => [['-Indexes', '+FollowSymLinks', '-Indexes'], 'Options -Indexes +FollowSymLinks'],
        ];
    }
}
