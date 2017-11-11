<?php

declare(strict_types=1);

namespace SiteTool;

use \Exception;
use \SimpleXMLElement;
use \Amp\Artax\Response;

/**
 * W3C Validator Class
 *
 * A simple standalone PHP class to validate remote web pages using the W3C validator API.
 *
 * @link http://validator.w3.org/docs/api.html
 */
class W3CValidator
{
    /**
     * Supported output formats
     */
    const _OUTPUT_FORMAT_CLI   = 'cli';
    const _OUTPUT_FORMAT_HTML  = 'html';

    const _DEFAULT_LIMIT        = 10;

    /**
     * API base URL
     *
     * @var string
     */
    const BASE_URL = 'http://validator.w3.org/check';

    /**
     * Results output XML document
     *
     * @var SimpleXMLElement
     */
    protected $document;

    public static function buildQueryUrl($uri)
    {
        $uri = self::BASE_URL . '?' . http_build_query([
            'output'=> 'soap12',
            'uri'   => $uri,
        ]);

        // 'User-Agent: Danack SiteTool', // This header is required by the W3C API

        return $uri;
    }

    public static function createFromResponse(Response $response)
    {
        return new self($response->getBody());
    }

    /**
     * @param $uri
     * @throws \Exception
     */
    public function __construct($output)
    {
        // Parse XML document
        $this->document = simplexml_load_string($output, null, null, 'http://schemas.xmlsoap.org/soap/envelope/');
        $this->document->registerXPathNamespace('env', 'http://www.w3.org/2003/05/soap-envelope/');
        $this->document->registerXPathNamespace('m', 'http://www.w3.org/2005/10/markup-validator');
    }

    /**
     * Get path
     *
     * Return XML node value using xpath syntax
     *
     * @param string $path
     * @param string $default
     * @return string
     */
    protected function getPath($path, $default = '')
    {
        $value = $this->document->xpath($path);
        return (!empty($value)) ? (string)$value[0] : $default;
    }

    /**
     * Get paths
     *
     * Return XML nodes values using xpath syntax
     *
     * @param string $path
     * @param array $default
     * @return string
     */
    protected function getPaths($path, $default = array())
    {
        $value = $this->document->xpath($path);
        return (!empty($value)) ? $value : $default;
    }

    /**
     * Is valid
     *
     * Returns true if page has a valid W3C markup or false if not.
     *
     * @return bool
     */
    public function isValid()
    {
        $result = $this->getPath('//m:validity');
        return ('true' === $result) ? true : false;
    }

    /**
     * Get URI
     *
     * Return validated document URI
     *
     * @return string
     */
    public function getURI()
    {
        return $this->getPath('//m:uri');
    }

    /**
     * Get errors
     *
     * Returns a list of all document markup errors.
     *
     * @param int $limit
     * @return array
     */
    public function getErrors($limit = self::_DEFAULT_LIMIT)
    {
        $list       = array();
        $elements   = $this->getPaths('//m:error');

        foreach ($elements as $i => $element) { /* @var $item SimpleXMLElement */
            if ($i == $limit) {
                break; // Reached limit number of warnings to return
            }

            $line       = $element->xpath('m:line');
            $line       = (!empty($line)) ? $line[0] : '';

            $column     = $element->xpath('m:col');
            $column     = (!empty($column)) ? $column[0] : '';

            $message    = $element->xpath('m:message');
            $message    = (!empty($message)) ? $message[0] : '';

            $list[] = array(
                'line'      => (string)$line,
                'column'    => (string)$column,
                'message'   => (string)$message,
            );
        }

        return $list;
    }

    /**
     * Get warnings
     *
     * Returns a list of all document markup warnings.
     *
     * @param int $limit
     * @return array
     */
    public function getWarnings($limit = self::_DEFAULT_LIMIT)
    {
        $list       = array();
        $elements   = $this->getPaths('//m:warning');

        foreach ($elements as $i => $element) { /* @var $item SimpleXMLElement */

            if ($i == $limit) {
                break; // Reached limit number of warnings to return
            }

            $line       = $element->xpath('m:line');
            $line       = (!empty($line)) ? $line[0] : '';

            $column     = $element->xpath('m:col');
            $column     = (!empty($column)) ? $column[0] : '';

            $message    = $element->xpath('m:message');
            $message    = (!empty($message)) ? $message[0] : '';

            $list[] = array(
                'line'      => (string)$line,
                'column'    => (string)$column,
                'message'   => (string)$message,
            );
        }

        return $list;
    }

    /**
     * Get errors count
     *
     * Returns an integer with the number of errors found in document.
     *
     * @return int
     */
    public function getErrorsCount()
    {
        return (int)$this->getPath('//m:errorcount');
    }

    /**
     * Get warnings count
     *
     * Returns an integer with the number of warnings found in document.
     *
     * @return int
     */
    public function getWarningsCount()
    {
        return (int)$this->getPath('//m:warningcount');
    }

    /**
     * Get report
     *
     * Output page error information report
     */
    public function getReport()
    {
        $output = array(
            'URI'           => $this->getURI(),
            'Validity'      => $this->isValid(),
            'Errors'        => $this->getErrorsCount(),
            'Error List'    => $this->getErrors(),
            'Warnings'      => $this->getErrorsCount(),
            'Warning List'  => $this->getErrors(),
        );

        return $output;
    }
}
