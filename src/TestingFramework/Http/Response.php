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
 * Model of response
 */
class Response
{
    const STATUS_Success = 'success';
    const STATUS_Failure = 'failure';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var null|string|array
     */
    protected $content;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var ResponseContent
     */
    protected $responseSection;

    /**
     * @param string $status
     * @param string $content
     * @param string $error
     */
    public function __construct($status, $content, $error)
    {
        $this->status = $status;
        $this->content = $content;
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array|null|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return ResponseContent
     */
    public function getResponseContent()
    {
        if (!isset($this->responseContent)) {
            $this->responseContent = new ResponseContent($this);
        }

        return $this->responseContent;
    }

    /**
     * @return null|array|ResponseSection[]
     */
    public function getResponseSections()
    {
        $sectionIdentifiers = func_get_args();

        if (empty($sectionIdentifiers)) {
            $sectionIdentifiers = ['Default'];
        }

        $sections = [];
        foreach ($sectionIdentifiers as $sectionIdentifier) {
            $sections[] = $this->getResponseContent()->getSection($sectionIdentifier);
        }

        return $sections;
    }
}
