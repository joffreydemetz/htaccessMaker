<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\MaintenanceRewrite;

class MaintenanceRewriteTest extends TestCase
{
    /**
     * Compared line by line without the blank lines (the blank line between the ON
     * and OFF sections has its own test).
     *
     * @param list<string> $expectedLines
     */
    #[DataProvider('processCases')]
    public function testProcess(array $config, array $expectedLines): void
    {
        $module = new MaintenanceRewrite();
        $module->process($config);

        $lines = array_values(array_filter(explode("\n", $module->toString()), static fn(string $line): bool => '' !== $line));

        $this->assertSame($expectedLines, $lines);
    }

    public static function processCases(): array
    {
        return [
            'off by default: the ON section commented out, IPs escaped' => [['allowedIps' => ['192.168.1.10', '10.0.0.1']], [
                'RewriteEngine On',
                '# RewriteCond %{REMOTE_ADDR} !^192\.168\.1\.10$',
                '# RewriteCond %{REMOTE_ADDR} !^10\.0\.0\.1$',
                '# RewriteCond %{REQUEST_URI} !^/maintenance.html$',
                '# RewriteRule $ /maintenance.html [L]',
                'RewriteCond %{REQUEST_URI} ^/maintenance.html$',
                'RewriteRule ^ https://%{HTTP_HOST}/ [L,R=301]',
            ]],
            'on: the OFF section commented out' => [['allowedIps' => ['192.168.1.10'], 'maintenanceFile' => '/down.html', 'defaultState' => true], [
                'RewriteEngine On',
                'RewriteCond %{REMOTE_ADDR} !^192\.168\.1\.10$',
                'RewriteCond %{REQUEST_URI} !^/down.html$',
                'RewriteRule $ /down.html [L]',
                '# RewriteCond %{REQUEST_URI} ^/down.html$',
                '# RewriteRule ^ https://%{HTTP_HOST}/ [L,R=301]',
            ]],
            'empty config adds nothing' => [[], ['RewriteEngine On']],
            // 1.1.4: an explicit enabled => false adds nothing (it rendered the defaults)
            'explicitly disabled adds nothing' => [['enabled' => false, 'allowedIps' => ['192.168.1.10']], ['RewriteEngine On']],
        ];
    }

    /** 1.1.4: the EmptyLine between the ON and OFF sections shows (Container::toString() dropped it) */
    public function testTheOnAndOffSectionsAreSeparatedByABlankLine(): void
    {
        $module = new MaintenanceRewrite();
        $module->process(['allowedIps' => ['192.168.1.10']]);

        $this->assertStringContainsString(
            "# RewriteRule $ /maintenance.html [L]\n\nRewriteCond %{REQUEST_URI} ^/maintenance.html$\n",
            $module->toString()
        );
    }
}
