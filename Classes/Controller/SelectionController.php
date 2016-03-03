<?php
namespace Fab\Vidi\Controller;

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

use Fab\Vidi\Domain\Model\Selection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller which handles actions related to Selection in Vidi Backend.
 */
class SelectionController extends ActionController
{

    /**
     * @var \Fab\Vidi\Domain\Repository\SelectionRepository
     * @inject
     */
    protected $selectionRepository;

    /**
     * @param Selection $selection
     */
    public function createAction(Selection $selection = NULL)
    {
        $selection->setDataType($this->getModuleLoader()->getDataType());

        $selection->setOwner($this->getBackendUser()->user['uid']);
        $this->selectionRepository->add($selection);
        $this->redirect('edit', 'Selection', 'vidi', array('dataType' => $selection->getDataType()));
    }

    /**
     * @param Selection $selection
     * @return string
     */
    public function deleteAction(Selection $selection)
    {
        $this->selectionRepository->remove($selection);
        return 'ok';
    }

    /**
     * @param Selection $selection
     */
    public function updateAction(Selection $selection)
    {
        $this->selectionRepository->update($selection);
        $this->redirect('show', 'Selection', 'vidi', array('selection' => $selection->getUid()));
    }

    /**
     * @param Selection $selection
     */
    public function showAction(Selection $selection)
    {
        $this->view->assign('selection', $selection);
    }

    /**
     * Returns an editing form for a given data type.
     *
     * @param string $dataType
     */
    public function editAction($dataType)
    {
        $selections = $this->selectionRepository->findByDataTypeForCurrentBackendUser($dataType);
        $this->view->assign('selections', $selections);
    }

    /**
     * @param string $dataType
     */
    public function listAction($dataType)
    {
        $selections = $this->selectionRepository->findByDataTypeForCurrentBackendUser($dataType);
        $this->view->assign('selections', $selections);
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

    /**
     * Returns an instance of the current Backend User.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

}