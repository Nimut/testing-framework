<?php
namespace Nimut\TestingFramework\Http;

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

/**
 * Model of response content
 */
class ResponseContent
{
    /**
     * @var ResponseSection[]
     */
    protected $sections;

    /**
     * @var array
     */
    protected $structure;

    /**
     * @var array
     */
    protected $structurePaths;

    /**
     * @var array
     */
    protected $records;

    /**
     * @var array
     */
    protected $queries;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $content = json_decode($response->getContent(), true);

        if ($content !== null && is_array($content)) {
            foreach ($content as $sectionIdentifier => $sectionData) {
                $section = new ResponseSection($sectionIdentifier, $sectionData);
                $this->sections[$sectionIdentifier] = $section;
            }
        }
    }

    /**
     * @param string $sectionIdentifier
     * @throws \RuntimeException
     * @return null|ResponseSection
     */
    public function getSection($sectionIdentifier)
    {
        if (isset($this->sections[$sectionIdentifier])) {
            return $this->sections[$sectionIdentifier];
        }

        throw new \RuntimeException('ResponseSection "' . $sectionIdentifier . '" does not exist');
    }
}
