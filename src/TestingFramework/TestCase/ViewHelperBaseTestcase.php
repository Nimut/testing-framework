<?php

namespace Nimut\TestingFramework\TestCase;

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

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\Rendering\RenderingContextFixture;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfigurationService;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * Base test class for testing view helpers
 */
abstract class ViewHelperBaseTestcase extends UnitTestCase
{
    /**
     * @var ViewHelperVariableContainer|ObjectProphecy
     */
    protected $viewHelperVariableContainer;

    /**
     * @var StandardVariableProvider
     */
    protected $templateVariableContainer;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @var TagBuilder
     */
    protected $tagBuilder;

    /**
     * @var []
     */
    protected $arguments;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RenderingContextFixture
     */
    protected $renderingContext;

    /**
     * @var MvcPropertyMappingConfigurationService
     */
    protected $mvcPropertyMapperConfigurationService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->viewHelperVariableContainer = $this->prophesize(ViewHelperVariableContainer::class);
        $this->uriBuilder = $this->getMockBuilder(UriBuilder::class)->getMock();
        $this->uriBuilder->expects($this->any())->method('reset')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setArguments')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setSection')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setFormat')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setCreateAbsoluteUri')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setAddQueryString')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setArgumentsToBeExcludedFromQueryString')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setLinkAccessRestrictedPages')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setTargetPageUid')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setTargetPageType')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setNoCache')->will($this->returnValue($this->uriBuilder));
        $this->uriBuilder->expects($this->any())->method('setAddQueryStringMethod')->will($this->returnValue($this->uriBuilder));
        $this->request = $this->prophesize(Request::class);
        $this->controllerContext = $this->getMockBuilder(ControllerContext::class)->getMock();
        $this->controllerContext->expects($this->any())->method('getUriBuilder')->will($this->returnValue($this->uriBuilder));
        $this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request->reveal()));
        $this->arguments = [];
        $this->templateVariableContainer = $this->getMockBuilder(StandardVariableProvider::class)->getMock();
        $this->tagBuilder = new TagBuilder();
        $this->renderingContext = $this->getAccessibleMock(RenderingContextFixture::class, ['getControllerContext']);
        $this->renderingContext->expects($this->any())->method('getControllerContext')->willReturn($this->controllerContext);
        $this->renderingContext->setVariableProvider($this->templateVariableContainer);
        $this->renderingContext->_set('viewHelperVariableContainer', $this->viewHelperVariableContainer->reveal());
        $this->renderingContext->setControllerContext($this->controllerContext);
        $this->mvcPropertyMapperConfigurationService = $this->getAccessibleMock(MvcPropertyMappingConfigurationService::class, ['dummy']);
    }

    /**
     * @param ViewHelperInterface|AbstractViewHelper $viewHelper
     * @return void
     */
    protected function injectDependenciesIntoViewHelper($viewHelper)
    {
        if (!$viewHelper instanceof ViewHelperInterface && !$viewHelper instanceof AbstractViewHelper) {
            throw new \RuntimeException(
                'Invalid viewHelper type "' . get_class($viewHelper) . '" in injectDependenciesIntoViewHelper',
                1487208085
            );
        }
        $viewHelper->setRenderingContext($this->renderingContext);
        $viewHelper->setArguments($this->arguments);
        // this condition is needed, because the (Be)/Security\*ViewHelper don't extend the
        // AbstractViewHelper and contain no method injectReflectionService()
        if ($viewHelper instanceof AbstractViewHelper && method_exists($viewHelper, 'injectReflectionService')) {
            $reflectionServiceProphecy = $this->prophesize(ReflectionService::class);
            $reflectionServiceProphecy->getMethodParameters(Argument::type('string'), Argument::type('string'))->willReturn([]);
            $viewHelper->injectReflectionService($reflectionServiceProphecy->reveal());
        }
        if ($viewHelper instanceof AbstractTagBasedViewHelper && $viewHelper instanceof AccessibleMockObjectInterface) {
            $viewHelper->_set('tag', $this->tagBuilder);
        }
    }

    /**
     * Helper function to merge arguments with default arguments according to their registration
     * This usually happens in ViewHelperInvoker before the view helper methods are called
     *
     * @param ViewHelperInterface $viewHelper
     * @param array $arguments
     */
    protected function setArgumentsUnderTest(ViewHelperInterface $viewHelper, array $arguments = [])
    {
        $argumentDefinitions = $viewHelper->prepareArguments();
        foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
            if (!isset($arguments[$argumentName])) {
                $arguments[$argumentName] = $argumentDefinition->getDefaultValue();
            }
        }
        $viewHelper->setArguments($arguments);
    }

    /**
     * Helper function for a valid mapping result
     */
    protected function stubRequestWithoutMappingErrors()
    {
        $this->request->getOriginalRequest()->willReturn(null);
        $this->request->getArguments()->willReturn([]);
        $result = $this->prophesize(Result::class);
        $result->forProperty('objectName')->willReturn($result->reveal());
        $result->forProperty('someProperty')->willReturn($result->reveal());
        $result->hasErrors()->willReturn(false);
        $this->request->getOriginalRequestMappingResults()->willReturn($result->reveal());
    }

    /**
     * Helper function for a mapping result with errors
     */
    protected function stubRequestWithMappingErrors()
    {
        $this->request->getOriginalRequest()->willReturn(null);
        $this->request->getArguments()->willReturn([]);
        $result = $this->prophesize(Result::class);
        $result->forProperty('objectName')->willReturn($result->reveal());
        $result->forProperty('someProperty')->willReturn($result->reveal());
        $result->hasErrors()->willReturn(true);
        $this->request->getOriginalRequestMappingResults()->willReturn($result->reveal());
    }

    /**
     * Helper function for the bound property
     *
     * @param $formObject
     */
    protected function stubVariableContainer($formObject)
    {
        $this->viewHelperVariableContainer->exists(Argument::cetera())->willReturn(true);
        $this->viewHelperVariableContainer->get(Argument::any(), 'formObjectName')->willReturn('objectName');
        $this->viewHelperVariableContainer->get(Argument::any(), 'fieldNamePrefix')->willReturn('fieldPrefix');
        $this->viewHelperVariableContainer->get(Argument::any(), 'formFieldNames')->willReturn([]);
        $this->viewHelperVariableContainer->get(Argument::any(), 'formObject')->willReturn($formObject);
        $this->viewHelperVariableContainer->get(Argument::any(), 'renderedHiddenFields')->willReturn([]);
        $this->viewHelperVariableContainer->addOrUpdate(Argument::cetera())->willReturn(null);
    }
}
