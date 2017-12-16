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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Collector for frontend data
 */
class Collector implements SingletonInterface
{
    /**
     * @var array
     */
    protected $tableFields;

    /**
     * @var array
     */
    protected $structure = [];

    /**
     * @var array
     */
    protected $structurePaths = [];

    /**
     * @var array
     */
    protected $records = [];

    /**
     * @var ContentObjectRenderer
     */
    public $cObj;

    public function addRecordData($content, array $configuration = null)
    {
        $recordIdentifier = $this->cObj->currentRecord;
        list($tableName) = explode(':', $recordIdentifier);
        $currentWatcherValue = $this->getCurrentWatcherValue();
        $position = strpos($currentWatcherValue, '/' . $recordIdentifier);

        $recordData = $this->filterFields($tableName, $this->cObj->data);
        $this->records[$recordIdentifier] = $recordData;

        if ($currentWatcherValue === $recordIdentifier) {
            $this->structure[$recordIdentifier] = $recordData;
            $this->structurePaths[$recordIdentifier] = [[]];
        } elseif (!empty($position)) {
            $levelIdentifier = substr($currentWatcherValue, 0, $position);
            $this->addToStructure($levelIdentifier, $recordIdentifier, $recordData);
        }
    }

    public function addFileData($content, array $configuration = null)
    {
        $currentFile = $this->cObj->getCurrentFile();

        if ($currentFile instanceof File) {
            $tableName = 'sys_file';
        } elseif ($currentFile instanceof FileReference) {
            $tableName = 'sys_file_reference';
        } else {
            return;
        }

        $recordData = $this->filterFields($tableName, $currentFile->getProperties());
        $recordIdentifier = $tableName . ':' . $currentFile->getUid();
        $this->records[$recordIdentifier] = $recordData;

        $currentWatcherValue = $this->getCurrentWatcherValue();
        $levelIdentifier = rtrim($currentWatcherValue, '/');
        $this->addToStructure($levelIdentifier, $recordIdentifier, $recordData);
    }

    /**
     * @param string $tableName
     * @param array $recordData
     * @return array
     */
    protected function filterFields($tableName, array $recordData)
    {
        $recordData = array_intersect_key(
            $recordData,
            array_flip($this->getTableFields($tableName))
        );

        return $recordData;
    }

    protected function addToStructure($levelIdentifier, $recordIdentifier, array $recordData)
    {
        $steps = explode('/', $levelIdentifier);
        $structurePaths = [];
        $structure = &$this->structure;

        foreach ($steps as $step) {
            list($identifier, $fieldName) = explode('.', $step);
            $structurePaths[] = $identifier;
            $structurePaths[] = $fieldName;
            if (!isset($structure[$identifier])) {
                return;
            }
            $structure = &$structure[$identifier];
            if (!isset($structure[$fieldName]) || !is_array($structure[$fieldName])) {
                $structure[$fieldName] = [];
            }
            $structure = &$structure[$fieldName];
        }

        $structure[$recordIdentifier] = $recordData;
        $this->structurePaths[$recordIdentifier][] = $structurePaths;
    }

    /**
     * @param string $content
     * @param null|array $configuration
     * @return void
     */
    public function attachSection($content, array $configuration = null)
    {
        $section = [
            'structure' => $this->structure,
            'structurePaths' => $this->structurePaths,
            'records' => $this->records,
        ];

        $as = (!empty($configuration['as']) ? $configuration['as'] : null);
        $this->getRenderer()->addSection($section, $as);
        $this->reset();
    }

    /**
     * @param string $tableName
     * @return array
     */
    protected function getTableFields($tableName)
    {
        if (!isset($this->tableFields) && !empty($this->getFrontendController()->tmpl->setup['config.']['watcher.']['tableFields.'])) {
            $this->tableFields = $this->getFrontendController()->tmpl->setup['config.']['watcher.']['tableFields.'];
            foreach ($this->tableFields as &$fieldList) {
                $fieldList = GeneralUtility::trimExplode(',', $fieldList, true);
            }
            unset($fieldList);
        }

        return !empty($this->tableFields[$tableName]) ? $this->tableFields[$tableName] : [];
    }

    /**
     * @return string
     */
    protected function getCurrentWatcherValue()
    {
        $watcherValue = null;
        if (isset($this->getFrontendController()->register['watcher'])) {
            $watcherValue = $this->getFrontendController()->register['watcher'];
        }

        return $watcherValue;
    }

    /**
     * @return Renderer
     */
    protected function getRenderer()
    {
        return GeneralUtility::makeInstance('Nimut\\TestingFramework\\Frontend\\Renderer');
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Collector needs to be reset after attaching a section, otherwise records will pile up.
     *
     * @return void
     */
    protected function reset()
    {
        $this->structure = [];
        $this->structurePaths = [];
        $this->records = [];
    }
}
