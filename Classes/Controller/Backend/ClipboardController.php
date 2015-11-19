<?php
namespace Fab\Vidi\Controller\Backend;

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
        return json_encode(TRUE);
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
     * @return \Fab\Vidi\Service\ClipboardService
     */
    protected function getClipboardService()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Service\ClipboardService');
    }

    /**
     * @return \Fab\Vidi\Service\ContentService
     */
    protected function getContentService()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Service\ContentService');
    }

}
