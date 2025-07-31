<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive\ErrorDocument;
use JDZ\HtaccessMaker\Directive\Comment;

/**
 * Container for managing custom error documents
 * 
 * Handles the configuration of custom error pages for different HTTP status codes.
 * Allows defining custom error pages that are shown when specific HTTP errors occur.
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ErrorDocuments extends Container
{
    protected array $defaults = [
        'errorDocuments' => [
            ['code' => 404, 'url' => '/error-404.html']
        ],
        'commonErrorPages' => null,
        'customErrors' => []
    ];

    /**
     * Process configuration array to generate error document directives
     * 
     * Expected configuration format:
     * [
     *   'errorDocuments' => [
     *     ['code' => 404, 'url' => '/errors/404.html'],
     *     ['code' => 500, 'url' => '/errors/500.html']
     *   ],
     *   'commonErrorPages' => '/errors',  // Optional: adds common error pages
     *   'customErrors' => [               // Optional: individual error mappings
     *     404 => '/custom-404.html',
     *     500 => '/custom-500.html'
     *   ]
     * ]
     * 
     * @param array $config Configuration array
     */
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        // Add error documents from array - handle both formats
        if ($config['errorDocuments']) {
            // Simple approach: if all keys are strings or integers and all values are strings, treat as simple format
            $isSimpleFormat = true;
            foreach ($config['errorDocuments'] as $key => $value) {
                if ((!is_string($key) && !is_int($key)) || !is_string($value)) {
                    $isSimpleFormat = false;
                    break;
                }
            }

            if ($isSimpleFormat) {
                // Simple format: ['404' => '/404.html', '500' => '/500.html'] or [404 => '/404.html', 500 => '/500.html']
                foreach ($config['errorDocuments'] as $code => $url) {
                    $this->addErrorDocument($code, $url);
                }
            } else {
                // Structured format: [['code' => 404, 'url' => '/404.html']]
                $this->addErrorDocuments($config['errorDocuments']);
            }
        }

        // Add common error pages if base URL provided
        if ($config['commonErrorPages']) {
            $this->addCommonErrorPages($config['commonErrorPages']);
        }

        // Add individual custom error mappings
        if ($config['customErrors']) {
            foreach ($config['customErrors'] as $code => $url) {
                $this->addErrorDocument($code, $url);
            }
        }
    }

    /**
     * Add a single error document mapping
     */
    public function addErrorDocument(int|string $code, string $url): self
    {
        $this->addDirective(new ErrorDocument($code, $url));
        return $this;
    }

    /**
     * Add multiple error document mappings
     */
    public function addErrorDocuments(array $errorDocuments): self
    {
        foreach ($errorDocuments as $errorDocument) {
            $this->addErrorDocument($errorDocument['code'], $errorDocument['url']);
        }
        return $this;
    }

    /**
     * Add common error document mappings
     */
    public function addCommonErrorPages(string $baseUrl = '/errors'): self
    {
        $commonErrors = [
            400 => 'bad-request.html',
            401 => 'unauthorized.html',
            403 => 'forbidden.html',
            404 => 'not-found.html',
            500 => 'internal-error.html',
            502 => 'bad-gateway.html',
            503 => 'service-unavailable.html'
        ];

        foreach ($commonErrors as $code => $filename) {
            $this->addErrorDocument($code, rtrim($baseUrl, '/') . '/' . $filename);
        }

        return $this;
    }
}
