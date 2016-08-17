<?php
namespace Fab\Vidi\ViewHelpers\Render;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Module\ModuleLoader;

/**
 * View helper for rendering components
 */
class ComponentsViewHelper extends AbstractViewHelper
{

    /**
     * Renders the position number of an content object.
     *
     * @param  string $part
     * @return string
     */
    public function render($part)
    {

        /** @var ModuleLoader $moduleLoader */
        $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);

        $getComponents = 'get' . ucfirst($part) . 'Components';
        $components = $moduleLoader->$getComponents();

        $result = '';
        foreach ($components as $component) {
            $viewHelper = $this->objectManager->get($component);

            // Get possible arguments but remove first one.
            $arguments = func_get_args();
            array_shift($arguments);
            $result .= call_user_func_array(array($viewHelper, 'render'), $arguments);
        }

        return $result;
    }
}
