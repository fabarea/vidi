<?php
namespace Fab\Vidi\ViewHelpers\Button;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which renders a button "work" for a Tool.
 */
class ToolWorkViewHelper extends AbstractViewHelper
{

    /**
     * Renders a button for "work" for a Tool.
     *
     * @param string $tool
     * @param string $label
     * @param array $arguments
     * @return string
     */
    public function render($tool, $label, $arguments = array())
    {

        $parameterPrefix = $this->getModuleLoader()->getParameterPrefix();

        // Compute the additional parameters.
        $additionalParameters = array(
            $parameterPrefix => array(
                'controller' => 'Tool',
                'action' => 'work',
                'tool' => $tool,
            ),
        );

        // Add possible additional arguments.
        if (!empty($arguments)) {
            $additionalParameters[$parameterPrefix]['arguments'] = $arguments;
        }

        $result = sprintf('<a href="%s&returnUrl=%s" class="btn btn-default">%s</a>',
            $this->getModuleLoader()->getModuleUrl($additionalParameters),
            urlencode($GLOBALS['_SERVER']['REQUEST_URI']),
            $label
        );
        return $result;
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Module\ModuleLoader
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
    }
}
