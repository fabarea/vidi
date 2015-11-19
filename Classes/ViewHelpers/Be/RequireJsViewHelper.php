<?php
namespace Fab\Vidi\ViewHelpers\Be;


/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

/**
 * Load RequireJS code.
 */
class RequireJsViewHelper extends AbstractBackendViewHelper
{

    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     * @inject
     */
    protected $pageRenderer;

    /**
     * Load RequireJS code.
     *
     * @return void
     * @api
     */
    public function render()
    {

        $content = $this->renderChildren();
        $this->pageRenderer->addJsInlineCode('vidi-inline', $content);

        $configuration['paths']['Fab/Vidi'] = '../typo3conf/ext/vidi/Resources/Public/JavaScript';
        $this->pageRenderer->addRequireJsConfiguration($configuration);
        $this->pageRenderer->loadRequireJsModule('Fab/Vidi/Vidi/Main');
    }

}
