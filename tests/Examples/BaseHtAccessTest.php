<?php

declare(strict_types=1);

namespace Tests\Examples;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The whole .htaccess assembled by examples/base.class.php (BaseHtAccess maps a YAML
 * config onto the containers and modules), compared with committed expected files:
 * tests/fixtures/<name>.yml -> tests/fixtures/<name>.htaccess.
 *
 * The configs leave out what renders a reported bug: maintenance mode, malicious request
 * blocking and user agent blacklists.
 */
class BaseHtAccessTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $examples = dirname(__DIR__, 2) . '/examples';

        require_once $examples . '/myhtaccess.php';
        require_once $examples . '/base.class.php';
    }

    #[DataProvider('configs')]
    public function testRendersTheExpectedFile(string $name): void
    {
        $fixtures = dirname(__DIR__) . '/fixtures';

        $htaccess = new \BaseHtAccess([$fixtures . '/' . $name . '.yml']);
        $htaccess->process();

        // the expected file may be checked out with CRLF line endings (core.autocrlf)
        $expected = str_replace("\r\n", "\n", (string) file_get_contents($fixtures . '/' . $name . '.htaccess'));

        $this->assertSame($expected, $htaccess->toString());
    }

    public static function configs(): array
    {
        return [
            'public website: comments, every module in its IfModule' => ['website'],
            'staging API: no comments, bare modules, basic auth' => ['api'],
        ];
    }
}
