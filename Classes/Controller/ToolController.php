<?php
namespace Fab\Vidi\Controller;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Fab\Vidi\Tool\ToolInterface;
use Fab\Vidi\Tool\ToolRegistry;

/**
 * Controller which handles tools related to a Vidi module.
 */
class ToolController extends ActionController
{

    /**
     * @return void
     */
    public function welcomeAction()
    {
        $items = [];
        $tools = ToolRegistry::getInstance()->getTools($this->getModuleLoader()->getDataType());

        foreach ($tools as $index => $tool) {
            $item = [];
            $item['title'] = $tool->getTitle();
            $item['description'] = $tool->getDescription();

            $items[] = $item;
        }
        $this->view->assign('items', $items);
    }

    /**
     * @param string $tool
     * @param array $arguments
     * @return void
     * @validate $tool Fab\Vidi\Domain\Validator\ToolValidator
     */
    public function workAction($tool, array $arguments = array())
    {
        /** @var ToolInterface $tool */
        $tool = GeneralUtility::makeInstance($tool);
        $workResult = $tool->work($arguments);
        $this->view->assign('result', $workResult);
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Module\ModuleLoader|object
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleLoader::class);
    }

}
