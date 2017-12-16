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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Renderer for frontend request data
 */
class Renderer implements SingletonInterface
{
    /**
     * @var array
     */
    protected $sections = [];

    /**
     * @param string $content
     * @param null|array $configuration
     * @return void
     */
    public function parseValues($content, array $configuration = null)
    {
        if (empty($content)) {
            return;
        }

        $values = json_decode($content, true);

        if (empty($values) || !is_array($values)) {
            return;
        }

        $asPrefix = (!empty($configuration['as']) ? $configuration['as'] . ':' : null);
        foreach ($values as $identifier => $structure) {
            $parser = $this->createParser();
            $parser->parse($structure);

            $section = [
                'structure' => $structure,
                'structurePaths' => $parser->getPaths(),
                'records' => $parser->getRecords(),
            ];

            $this->addSection($section, $asPrefix . $identifier);
        }
    }

    /**
     * @param array $section
     * @param null|string $as
     */
    public function addSection(array $section, $as = null)
    {
        if (!empty($as)) {
            $this->sections[$as] = $section;
        } else {
            $this->sections[] = $section;
        }
    }

    /**
     * @param string $content
     * @param null|array $configuration
     * @return string
     */
    public function renderSections($content, array $configuration = null)
    {
        $content = json_encode($this->sections);

        return $content;
    }

    /**
     * @return Parser
     */
    protected function createParser()
    {
        return GeneralUtility::makeInstance('Nimut\\TestingFramework\\Frontend\\Parser');
    }
}
