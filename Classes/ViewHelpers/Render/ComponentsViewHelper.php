<?php
namespace Fab\Vidi\ViewHelpers\Render;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for rendering components
 */
class ComponentsViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('part', 'string', 'Template part', true);
    }

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs strip_tags() function.
     *
     * @return mixed
     */
    public function render()
    {
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * Applies strip_tags() on the specified value.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var ModuleLoader $moduleLoader */
        $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);

        $part = $arguments['part'];

        $getComponents = 'get' . ucfirst($part) . 'Components';
        $components = $moduleLoader->$getComponents();

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $result = '';
        foreach ($components as $component) {
            $viewHelper = $objectManager->get($component);

            // Get possible arguments but remove first one.
            $arguments = func_get_args();
            array_shift($arguments);
            $result .= call_user_func_array(array($viewHelper, 'render'), $arguments);
        }

        return $result;
    }

}
