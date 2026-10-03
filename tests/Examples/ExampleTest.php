<?php

declare(strict_types=1);

namespace Tests\Examples;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\HtPasswd;

class ExampleTest extends TestCase
{
    private string $examplesDir;
    private string $exportsDir;

    protected function setUp(): void
    {
        $this->examplesDir = dirname(__DIR__, 2) . '/examples';
        $this->exportsDir = $this->examplesDir . '/exports';

        // Clean up any existing export files
        $this->cleanupExports();
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $this->cleanupExports();
    }

    private function cleanupExports(): void
    {
        if (is_dir($this->exportsDir)) {
            $files = glob($this->exportsDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function testExamplePhpRequiredFilesExist(): void
    {
        $this->assertFileExists($this->examplesDir . '/example.php');
        $this->assertFileExists($this->examplesDir . '/myhtaccess.php');
        $this->assertFileExists($this->examplesDir . '/base.class.php');
    }

    public function testRequiredConfigFilesExist(): void
    {
        $configFiles = [
            'core.yml',
            'api.yml',
            'web.yml',
            'front.yml',
            'website.yml',
            'dev.yml'
        ];

        foreach ($configFiles as $configFile) {
            $this->assertFileExists($this->examplesDir . '/config/' . $configFile);
        }
    }

    public function testExamplePhpCanBeIncluded(): void
    {
        // Capture output to prevent cluttering test output
        ob_start();

        // Include required files
        require_once $this->examplesDir . '/myhtaccess.php';
        require_once $this->examplesDir . '/base.class.php';

        $output = ob_get_clean();

        // Verify that no fatal errors occurred and classes are available
        $this->assertTrue(class_exists('\Config'));
        $this->assertTrue(class_exists('\MyHtAccess'));
        $this->assertTrue(class_exists('\BaseHtAccess'));
        $this->assertTrue(function_exists('createHtAccessFromConfig'));
    }

    public function testCreateHtAccessFromConfigFunction(): void
    {
        // Include required files
        require_once $this->examplesDir . '/myhtaccess.php';
        require_once $this->examplesDir . '/base.class.php';

        // Ensure exports directory exists
        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0777, true);
        }

        // Capture output
        ob_start();

        // Test creating API htaccess
        createHtAccessFromConfig([
            $this->examplesDir . '/config/core.yml',
            $this->examplesDir . '/config/api.yml'
        ], 'test_api', \BaseHtAccess::class);

        $output = ob_get_clean();

        // Verify output contains expected messages
        $this->assertStringContainsString('test_api', $output);
        $this->assertStringContainsString('.htaccess ... OK', $output);

        // Verify htaccess file was created
        $this->assertFileExists($this->exportsDir . '/test_api.htaccess');

        // Verify htaccess content is valid
        $content = file_get_contents($this->exportsDir . '/test_api.htaccess');
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('RewriteBase /api', $content);
    }

    public function testCreateHtAccessFromConfigWithWebConfig(): void
    {
        // Include required files
        require_once $this->examplesDir . '/myhtaccess.php';
        require_once $this->examplesDir . '/base.class.php';

        // Ensure exports directory exists
        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0777, true);
        }

        // Capture output
        ob_start();

        // Test creating website htaccess
        createHtAccessFromConfig([
            $this->examplesDir . '/config/core.yml',
            $this->examplesDir . '/config/web.yml',
            $this->examplesDir . '/config/front.yml',
            $this->examplesDir . '/config/website.yml',
        ], 'test_website', \BaseHtAccess::class);

        $output = ob_get_clean();

        // Verify output contains expected messages
        $this->assertStringContainsString('test_website', $output);
        $this->assertStringContainsString('.htaccess ... OK', $output);

        // Verify htaccess file was created
        $this->assertFileExists($this->exportsDir . '/test_website.htaccess');

        // Verify htaccess content contains expected directives
        $content = file_get_contents($this->exportsDir . '/test_website.htaccess');
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('ServerSignature Off', $content);
    }

    public function testConfigClassFunctionality(): void
    {
        require_once $this->examplesDir . '/myhtaccess.php';

        $config = new \Config();
        $config->loadFromFile($this->examplesDir . '/config/core.yml');

        // Test that config can load and retrieve values
        $this->assertFalse($config->getBool('redirectWww'));
        $this->assertTrue($config->getBool('forceSsl'));
        $this->assertEquals('test', $config->get('htPasswordUser'));
    }

    public function testMyHtAccessClassFunctionality(): void
    {
        require_once $this->examplesDir . '/myhtaccess.php';

        $configFiles = [
            $this->examplesDir . '/config/core.yml',
            $this->examplesDir . '/config/api.yml'
        ];

        $htaccess = new \MyHtAccess($configFiles);

        $this->assertInstanceOf(\MyHtAccess::class, $htaccess);
        $this->assertInstanceOf(\Config::class, $htaccess->config);

        // Test that config values are properly loaded
        $this->assertEquals('/api', $htaccess->config->get('rewriteBase'));
        $this->assertTrue($htaccess->config->getBool('httpAuthorization'));
    }

    public function testBaseHtAccessClassFunctionality(): void
    {
        require_once $this->examplesDir . '/myhtaccess.php';
        require_once $this->examplesDir . '/base.class.php';

        $configFiles = [
            $this->examplesDir . '/config/core.yml',
            $this->examplesDir . '/config/api.yml'
        ];

        $htaccess = new \BaseHtAccess($configFiles);
        $htaccess->process();

        $this->assertInstanceOf(\BaseHtAccess::class, $htaccess);

        $output = $htaccess->toString();
        $this->assertNotEmpty($output);

        // Verify specific directives are present based on config
        $this->assertStringContainsString('ServerSignature Off', $output);
        $this->assertStringContainsString('RewriteBase /api', $output);
    }

    public function testExamplePhpExecutesWithoutErrors(): void
    {
        // Ensure exports directory exists
        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0777, true);
        }

        // Capture output and execute example.php
        ob_start();

        $exitCode = 0;
        $errorOutput = '';

        try {
            include $this->examplesDir . '/example.php';
        } catch (\Throwable $e) {
            $exitCode = 1;
            $errorOutput = $e->getMessage();
        }

        $output = ob_get_clean();

        // Verify execution was successful
        $this->assertEquals(0, $exitCode, "Example.php execution failed: " . $errorOutput);

        // Verify output contains expected content
        $this->assertStringContainsString('website', $output);
        $this->assertStringContainsString('api', $output);
        $this->assertStringContainsString('dev', $output);

        // Verify all expected files were created
        $expectedFiles = ['website.htaccess', 'api.htaccess', 'dev.htaccess'];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($this->exportsDir . '/' . $file, "File $file was not created");

            $content = file_get_contents($this->exportsDir . '/' . $file);
            $this->assertNotEmpty($content, "File $file is empty");
        }
    }

    public function testErrorHandlingInCreateHtAccessFromConfig(): void
    {
        require_once $this->examplesDir . '/myhtaccess.php';
        require_once $this->examplesDir . '/base.class.php';

        // Ensure exports directory exists
        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0777, true);
        }

        // Capture output
        ob_start();

        // Test with non-existent config file
        createHtAccessFromConfig([
            $this->examplesDir . '/config/nonexistent.yml'
        ], 'test_error', \BaseHtAccess::class);

        $output = ob_get_clean();

        // Verify error handling
        $this->assertStringContainsString('test_error', $output);
        $this->assertStringContainsString('KO', $output);
    }
}
