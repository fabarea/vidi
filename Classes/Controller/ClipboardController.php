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
use Fab\Vidi\Persistence\MatcherObjectFactory;

/**
 * Controller which handles actions related to Vidi in the Backend.
 */
class ClipboardController extends ActionController
{

    /**
     * Save data into the clipboard.
     *
     * @param array $matches
     * @return string
     */
    public function saveAction(array $matches = array())
    {

        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);
        $this->getClipboardService()->save($matcher);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);
        $numberOfObjects = $contentService->getNumberOfObjects();

        if ($numberOfObjects === 0) {
            $this->getClipboardService()->flush();
        }

        # Json header is not automatically sent in the BE...
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->sendHeaders();
        return json_encode($numberOfObjects);
    }

    /**
     * Completely flush the clipboard.
     *
     * @return string
     */
    public function flushAction()
    {
        $this->getClipboardService()->flush();

        # Json header is not automatically sent in the BE...
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->sendHeaders();
        return json_encode(true);
    }

    /**
     * Show the content of the clipboard.
     *
     * @return string
     */
    public function showAction()
    {

        // Retrieve matcher object from clipboard.
        $matcher = $this->getClipboardService()->getMatcher();

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        // count number of items and display it.
        $this->view->assign('target', GeneralUtility::_GP('id'));
        $this->view->assign('numberOfObjects', $contentService->getNumberOfObjects());
        $this->view->assign('objects', $contentService->getObjects());
    }

    /**
     * @return \Fab\Vidi\Service\ClipboardService|object
     */
    protected function getClipboardService()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Service\ClipboardService::class);
    }

    /**
     * @return \Fab\Vidi\Service\ContentService|object
     */
    protected function getContentService()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Service\ContentService::class);
    }

}
