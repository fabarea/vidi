<?php
namespace Fab\Vidi\ViewHelpers\Be;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

/**
 * Load the assets (JavaScript, CSS) for this Vidi module.
 */
class AdditionalAssetsViewHelper extends AbstractBackendViewHelper
{

    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     * @Inject
     */
    protected $pageRenderer;

    /**
     * Load the assets (JavaScript, CSS) for this Vidi module.
     *
     * @return void
     * @api
     */
    public function render()
    {
        /** @var ModuleLoader $moduleLoader */
        $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);

        foreach ($moduleLoader->getAdditionalStyleSheetFiles() as $addCssFile) {
            $fileNameAndPath = $this->resolvePath($addCssFile);
            $this->pageRenderer->addCssFile($fileNameAndPath);
        }

        foreach ($moduleLoader->getAdditionalJavaScriptFiles() as $addJsFile) {
            $fileNameAndPath = $this->resolvePath($addJsFile);
            $this->pageRenderer->addJsFile($fileNameAndPath);
        }
    }

    /**
     * Resolve a resource path.
     *
     * @param string $uri
     * @return string
     */
    protected function resolvePath($uri)
    {
        $uri = GeneralUtility::getFileAbsFileName($uri);
        $uri = substr($uri, strlen(PATH_site));
        if (TYPO3_MODE === 'BE' && $uri !== false) {
            $uri = '../' . $uri;
        }
        return $uri;
    }

}
