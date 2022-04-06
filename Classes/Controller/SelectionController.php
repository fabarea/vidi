<?php
namespace Fab\Vidi\Controller;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Model\Selection;
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @deprecated selection feature has been removed
 * Controller which handles actions related to Selection in Vidi Backend.
 */
class SelectionController extends ActionController
{

    /**
     * @var \Fab\Vidi\Domain\Repository\SelectionRepository
     * @Inject
     */
    public $selectionRepository;

    /**
     * @param Selection $selection
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     */
    public function createAction(Selection $selection = null)
    {
        $selection->setDataType($this->getModuleLoader()->getDataType());

        $selection->setOwner($this->getBackendUser()->user['uid']);
        $this->selectionRepository->add($selection);
        $this->redirect('edit', 'Selection', 'vidi', array('dataType' => $selection->getDataType()));
    }

    /**
     * @param Selection $selection
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteAction(Selection $selection)
    {
        $this->selectionRepository->remove($selection);
        return 'ok';
    }

    /**
     * @param Selection $selection
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
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
     * @return ModuleLoader
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
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
