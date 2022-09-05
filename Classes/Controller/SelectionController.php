<?php
namespace Fab\Vidi\Controller;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Psr\Http\Message\ResponseInterface;
use Fab\Vidi\Domain\Repository\SelectionRepository;
use Fab\Vidi\Exception\InvalidKeyInArrayException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use Fab\Vidi\Domain\Model\Selection;
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @deprecated selection feature has been removed
 * Controller which handles actions related to Selection in Vidi Backend.
 */
class SelectionController extends ActionController
{

    /**
     * @param Selection $selection
     */
    public function createAction(Selection $selection = null)
    {
        $selectionRepository = GeneralUtility::makeInstance(SelectionRepository::class);
        $selection->setDataType($this->getModuleLoader()->getDataType());

        $selection->setOwner($this->getBackendUser()->user['uid']);
        $selectionRepository->add($selection);
        $this->redirect('edit', 'Selection', 'vidi', array('dataType' => $selection->getDataType()));
    }

    /**
     * @param Selection $selection
     * @return string
     */
    public function deleteAction(Selection $selection): ResponseInterface
    {
        $selectionRepository = GeneralUtility::makeInstance(SelectionRepository::class);
        $selectionRepository->remove($selection);
        return $this->htmlResponse('ok');
    }

    /**
     * @param Selection $selection
     */
    public function updateAction(Selection $selection)
    {
        $selectionRepository = GeneralUtility::makeInstance(SelectionRepository::class);
        $selectionRepository->update($selection);
        $this->redirect('show', 'Selection', 'vidi', array('selection' => $selection->getUid()));
    }

    /**
     * @param Selection $selection
     */
    public function showAction(Selection $selection): ResponseInterface
    {
        $this->view->assign('selection', $selection);
        return $this->htmlResponse();
    }

    /**
     * Returns an editing form for a given data type.
     *
     * @param string $dataType
     */
    public function editAction($dataType): ResponseInterface
    {
        $selectionRepository = GeneralUtility::makeInstance(SelectionRepository::class);
        $selections = $selectionRepository->findByDataTypeForCurrentBackendUser($dataType);
        $this->view->assign('selections', $selections);
        return $this->htmlResponse();
    }

    /**
     * @param string $dataType
     */
    public function listAction($dataType): ResponseInterface
    {
        $selectionRepository = GeneralUtility::makeInstance(SelectionRepository::class);
        $selections = $selectionRepository->findByDataTypeForCurrentBackendUser($dataType);
        $this->view->assign('selections', $selections);
        return $this->htmlResponse();
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
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

}
