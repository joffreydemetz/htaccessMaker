<?php

declare(strict_types=1);

namespace Tests\Examples;

use PHPUnit\Framework\TestCase;

/**
 * examples/example.php runs as documented. It writes into examples/exports/ (hard-coded
 * by the example, gitignored), emptied before and after each test.
 */
class ExampleTest extends TestCase
{
    private string $examplesDir;
    private string $exportsDir;

    protected function setUp(): void
    {
        $this->examplesDir = dirname(__DIR__, 2) . '/examples';
        $this->exportsDir = $this->examplesDir . '/exports';

        $this->cleanupExports();
    }

    protected function tearDown(): void
    {
        $this->cleanupExports();
    }

    private function cleanupExports(): void
    {
        if (is_dir($this->exportsDir)) {
            foreach (glob($this->exportsDir . '/*') as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function testExamplePhpExecutesWithoutErrors(): void
    {
        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0777, true);
        }

        ob_start();
        try {
            include $this->examplesDir . '/example.php';
        } finally {
            $output = ob_get_clean();
        }

        $separator = "-----------------------------\n\n";
        $this->assertSame(
            "website\n.htaccess ... OK \n" . $separator
                . "api\n.htaccess ... OK \n" . $separator
                . "dev\n.htaccess ... OK \n.htpasswd ... OK \n" . $separator,
            $output
        );

        foreach (['website', 'api', 'dev'] as $name) {
            $this->assertStringStartsWith('ServerSignature Off', (string) file_get_contents($this->exportsDir . '/' . $name . '.htaccess'));
        }
        $this->assertMatchesRegularExpression(
            '/^devuser:\$apr1\$[.\/0-9A-Za-z]{8}\$[.\/0-9A-Za-z]{22}\n$/D',
            (string) file_get_contents($this->exportsDir . '/dev.htpasswd')
        );
    }

    public function testErrorHandlingInCreateHtAccessFromConfig(): void
    {
        require_once $this->examplesDir . '/myhtaccess.php';
        require_once $this->examplesDir . '/base.class.php';

        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0777, true);
        }

        ob_start();
        try {
            createHtAccessFromConfig([$this->examplesDir . '/config/nonexistent.yml'], 'test_error', \BaseHtAccess::class);
        } finally {
            $output = ob_get_clean();
        }

        $this->assertStringStartsWith("test_error\n.htaccess ... KO \n", $output);
        $this->assertFileDoesNotExist($this->exportsDir . '/test_error.htaccess');
    }
}
