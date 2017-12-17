<?php
namespace Nimut\TestingFramework\Frontend;

/*
 * This file is part of the NIMUT testing-framework project.
 *
 * It was taken from the TYPO3 CMS project (www.typo3.org).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Parser for frontend request data
 */
class Parser implements SingletonInterface
{
    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var array
     */
    protected $records = [];

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @param array $structure
     * @param array $path
     */
    public function parse(array $structure, array $path = [])
    {
        $this->process($structure);
    }

    /**
     * @param array $iterator
     * @param array $path
     */
    protected function process(array $iterator, array $path = [])
    {
        foreach ($iterator as $identifier => $properties) {
            $this->addRecord($identifier, $properties);
            $this->addPath($identifier, $path);
            foreach ($properties as $propertyName => $propertyValue) {
                if (!is_array($propertyValue)) {
                    continue;
                }
                $nestedPath = array_merge($path, [$identifier, $propertyName]);
                $this->process($propertyValue, $nestedPath);
            }
        }
    }

    /**
     * @param string $identifier
     * @param array $properties
     */
    protected function addRecord($identifier, array $properties)
    {
        if (isset($this->records[$identifier])) {
            return;
        }

        foreach ($properties as $propertyName => $propertyValue) {
            if (is_array($propertyValue)) {
                unset($properties[$propertyName]);
            }
        }

        $this->records[$identifier] = $properties;
    }

    /**
     * @param string $identifier
     * @param array $path
     */
    protected function addPath($identifier, array $path)
    {
        if (!isset($this->paths[$identifier])) {
            $this->paths[$identifier] = [];
        }

        $this->paths[$identifier][] = $path;
    }
}
