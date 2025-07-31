<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive\AddType;

/**
 * MimeTypes Container
 * 
 * This container is responsible for handling MIME type definitions
 * in the .htaccess file. It allows you to add custom MIME types,
 * set common web types, image types, and document types.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class MimeTypes extends Container
{
    protected array $defaults = [
        'mimeTypes' => [],
        'includeCommonWeb' => false,
        'includeImages' => false,
        'includeDocuments' => false,
    ];

    protected function parseConfig(array $config): array|false
    {
        $config = parent::parseConfig($config);

        if ($config && empty($config['mimeTypes'])) {
            $config['includeCommonWeb'] = true;
        }

        if (false === $config) {
            return false;
        }

        return $config;
    }

    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        // Add custom MIME types
        if ($config['mimeTypes']) {
            $this->addMimeTypes($config['mimeTypes']);
        }

        // Add common web types if requested
        if ($config['includeCommonWeb']) {
            $this->addCommonWebTypes();
        }

        // Add image types if requested
        if ($config['includeImages']) {
            $this->addImageTypes();
        }

        // Add document types if requested
        if ($config['includeDocuments']) {
            $this->addDocumentTypes();
        }
    }

    /**
     * Add a single MIME type with its file extensions
     */
    public function addMimeType(string $mimeType, array $extensions): self
    {
        if ($mimeType && $extensions) {
            $this->addDirective(new AddType($mimeType . ' ' . implode(' ', $extensions)));
        }
        return $this;
    }

    /**
     * Add multiple MIME types
     */
    public function addMimeTypes(array $mimeTypes): self
    {
        foreach ($mimeTypes as $key => $value) {
            // Handle both formats:
            // Simple: ['application/json' => '.json', 'text/css' => '.css']
            // Structured: [['type' => 'application/json', 'extensions' => ['.json']]]
            if (is_string($key)) {
                // Simple format: key is mime type, value is extension(s)
                $extensions = is_array($value) ? $value : [$value];
                $this->addMimeType($key, $extensions);
            } else {
                // Structured format: value is array with 'type' and 'extensions'
                $this->addMimeType($value['type'], $value['extensions']);
            }
        }
        return $this;
    }

    /**
     * Add common web MIME types
     */
    public function addCommonWebTypes(): self
    {
        $commonTypes = [
            ['type' => 'text/css', 'extensions' => ['.css']],
            ['type' => 'text/javascript', 'extensions' => ['.js']],
            ['type' => 'application/javascript', 'extensions' => ['.js']],
            ['type' => 'application/json', 'extensions' => ['.json']],
            ['type' => 'application/xml', 'extensions' => ['.xml']],
            ['type' => 'text/xml', 'extensions' => ['.xml']],
            ['type' => 'image/svg+xml', 'extensions' => ['.svg', '.svgz']],
            ['type' => 'image/x-icon', 'extensions' => ['.ico']],
            ['type' => 'text/plain', 'extensions' => ['.txt']],
            ['type' => 'font/woff', 'extensions' => ['.woff']],
            ['type' => 'font/woff2', 'extensions' => ['.woff2']],
            ['type' => 'font/ttf', 'extensions' => ['.ttf']],
            ['type' => 'font/otf', 'extensions' => ['.otf']],
            ['type' => 'font/eot', 'extensions' => ['.eot']],
        ];

        return $this->addMimeTypes($commonTypes);
    }

    /**
     * Add common image MIME types
     */
    public function addImageTypes(): self
    {
        $imageTypes = [
            ['type' => 'image/jpeg', 'extensions' => ['.jpg', '.jpeg']],
            ['type' => 'image/png', 'extensions' => ['.png']],
            ['type' => 'image/gif', 'extensions' => ['.gif']],
            ['type' => 'image/webp', 'extensions' => ['.webp']],
            ['type' => 'image/avif', 'extensions' => ['.avif']],
            ['type' => 'image/bmp', 'extensions' => ['.bmp']],
            ['type' => 'image/tiff', 'extensions' => ['.tiff', '.tif']],
            ['type' => 'image/x-icon', 'extensions' => ['.ico']],
        ];

        return $this->addMimeTypes($imageTypes);
    }

    /**
     * Add common document MIME types
     */
    public function addDocumentTypes(): self
    {
        $documentTypes = [
            ['type' => 'application/pdf', 'extensions' => ['.pdf']],
            ['type' => 'application/msword', 'extensions' => ['.doc']],
            ['type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'extensions' => ['.docx']],
            ['type' => 'application/vnd.ms-excel', 'extensions' => ['.xls']],
            ['type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'extensions' => ['.xlsx']],
            ['type' => 'application/vnd.ms-powerpoint', 'extensions' => ['.ppt']],
            ['type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'extensions' => ['.pptx']],
        ];

        return $this->addMimeTypes($documentTypes);
    }
}
