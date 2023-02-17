<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Helper;

class ArrayHelper
{
    /**
     * @param string $pathPartsDelimiter
     */
    public function __construct(
        private string $pathPartsDelimiter = '.',
    ) {
    }

    /**
     * @param array $modifiedData
     * @param string $rootPath
     *
     * @return array<string>
     */
    public function getElementsPaths(array $modifiedData, string $rootPath = ''): array
    {
        $paths = [];
        foreach ($modifiedData as $key => $value) {
            $path = implode($this->pathPartsDelimiter, array_filter([$rootPath, $key]));

            if (is_array($value)) {
                foreach ($this->getElementsPaths($value, $path) as $childPath) {
                    $paths[] = $childPath;
                }
            } else {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @param array $data
     * @param string $elementPath
     *
     * @return mixed
     */
    public function getElementValue(array $data, string $elementPath): mixed
    {
        $element = &$data;
        foreach (explode($this->pathPartsDelimiter, $elementPath) as $key) {
            if (array_key_exists($key, $element)) {
                $element = &$element[$key];
            } else {
                return null;
            }
        }

        return $element;
    }

    /**
     * @param array $data
     * @param string $elementPath
     * @param string $elementValue
     *
     * @return array
     */
    public function setElementValue(array $data, string $elementPath, string $elementValue): array
    {
        $element = &$data;
        foreach (explode($this->pathPartsDelimiter, $elementPath) as $key) {
            if (array_key_exists($key, $element)) {
                $element = &$element[$key];
            } else {
                return $data;
            }
        }

        $element = $elementValue;

        return $data;
    }
}
