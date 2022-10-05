<?php

namespace Fab\Vidi\ViewHelpers\Be;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

/**
 * Load RequireJS code.
 */
class RequireJsViewHelper extends AbstractBackendViewHelper
{
    /**
     * Load RequireJS code.
     *
     * @return void
     */
    public function render()
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        $content = $this->renderChildren();
        $pageRenderer->addJsInlineCode('vidi-inline', $content);

        $publicResourcesPath = PathUtility::getPublicResourceWebPath('EXT:vidi/Resources/Public/');
        $configuration['paths']['Fab/Vidi'] = $publicResourcesPath . 'JavaScript';
        $pageRenderer->addRequireJsConfiguration($configuration);
        $pageRenderer->loadRequireJsModule('Fab/Vidi/Vidi/Main');
    }
}
