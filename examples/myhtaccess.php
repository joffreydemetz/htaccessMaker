<?php

use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\HtPasswd;
use Symfony\Component\Yaml\Yaml;
use JDZ\Utils\Data as jData;

class Config extends jData
{
    public function loadFromFile(string $filename, bool $merge = true): self
    {
        $data = Yaml::parseFile($filename);
        $this->sets($data, $merge);
        return $this;
    }
}

class MyHtAccess extends HtAccess
{
    public bool $showComments = false;
    public Config $config;

    public function __construct(array $configFiles = [])
    {
        $this->config = new Config();

        foreach ($configFiles as $file) {
            $this->config->loadFromFile($file, true);
        }

        $this->showComments = $this->config->get('showComments') ?? false;
        $this->ensureApacheCompatibility = $this->config->get('ensureApacheCompatibility') ?? false;
    }

    public function process(): void
    {
        $this->withComments($this->config->getBool('showComments', $this->showComments));
        $this->withApacheCompatibility($this->config->getBool('ensureApacheCompatibility', $this->ensureApacheCompatibility));
    }
}

function createHtAccessFromConfig(array $configFiles, string $filename, string $htAccessClass): void
{
    try {
        echo "$filename\n";
        echo ".htaccess ... ";

        if (file_exists(__DIR__ . '/exports/' . $filename . '.htaccess')) {
            unlink(__DIR__ . '/exports/' . $filename . '.htaccess');
        }

        if (file_exists(__DIR__ . '/exports/' . $filename . '.htpasswd')) {
            unlink(__DIR__ . '/exports/' . $filename . '.htpasswd');
        }

        $htaccess = new $htAccessClass($configFiles);
        $htaccess->process();

        file_put_contents(__DIR__ . '/exports/' . $filename . '.htaccess', $htaccess->toString());
        echo "OK \n";

        if ($htaccess->config->getBool('serverAuthorization')) {
            echo ".htpasswd ... ";

            $htpasswd = new HtPasswd();
            $htpasswd->addUser($htaccess->config->get('htPasswordUser'), $htaccess->config->get('htPasswordClear'));
            file_put_contents(__DIR__ . '/exports/' . $filename . '.htpasswd', $htpasswd->toString());
            echo "OK \n";
        }
    } catch (\Throwable $e) {
        echo "KO \n";
        echo (string)$e;
        echo "\n";
    }

    echo "-----------------------------\n";
    echo "\n";
}

if (!\is_dir(__DIR__ . '/exports/')) {
    \mkdir(__DIR__ . '/exports/', 0777, true);
}

if (!\is_dir(__DIR__ . '/config/')) {
    \mkdir(__DIR__ . '/config/', 0777, true);
}
