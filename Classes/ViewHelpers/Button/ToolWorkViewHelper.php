<?php

namespace Fab\Vidi\ViewHelpers\Button;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which renders a button "work" for a Tool.
 */
class ToolWorkViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('tool', 'string', '', true);
        $this->registerArgument('label', 'string', '', true);
        $this->registerArgument('arguments', 'array', '', false, []);
    }

    /**
     * Renders a button for "work" for a Tool.
     *
     * @return string
     */
    public function render()
    {
        $tool = $this->arguments['tool'];
        $label = $this->arguments['label'];
        $arguments = $this->arguments['arguments'];

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

        $result = sprintf(
            '<a href="%s&returnUrl=%s" class="btn btn-default">%s</a>',
            $this->getModuleLoader()->getModuleUrl($additionalParameters),
            urlencode($GLOBALS['_SERVER']['REQUEST_URI']),
            $label
        );
        return $result;
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return ModuleLoader|object
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }
}
