<?php
namespace Fab\Vidi\Controller;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Psr\Http\Message\ResponseInterface;
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
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
    public function welcomeAction(): ResponseInterface
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
        return $this->htmlResponse();
    }

    /**
     * @param string $tool
     * @param array $arguments
     * @return void
     * @Extbase\Validate("Fab\Vidi\Domain\Validator\ToolValidator", param="tool")
     */
    public function workAction(string $tool, array $arguments = array()): ResponseInterface
    {
        /** @var ToolInterface $tool */
        $tool = GeneralUtility::makeInstance($tool);
        $workResult = $tool->work($arguments);
        $this->view->assign('result', $workResult);
        return $this->htmlResponse();
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
