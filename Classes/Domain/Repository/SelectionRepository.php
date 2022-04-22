<?php
namespace Fab\Vidi\Domain\Repository;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use Fab\Vidi\Domain\Model\Selection;

/**
 * Repository for accessing Selections
 */
class SelectionRepository extends Repository
{

    /**
     * @param string $dataType
     * @return QueryResult
     */
    public function findByDataTypeForCurrentBackendUser($dataType)
    {
        $query = $this->createQuery();

        // Compute the OR part
        if ($this->getBackendUser()->isAdmin()) {
            $logicalOr = $query->logicalOr(
                $query->equals('visibility', Selection::VISIBILITY_EVERYONE),
                $query->equals('visibility', Selection::VISIBILITY_ADMIN_ONLY),
                $query->equals('cruser_id', $this->getBackendUser()->user['uid'])
            );
        } else {
            $logicalOr = $query->logicalOr(
                $query->equals('visibility', Selection::VISIBILITY_EVERYONE),
                $query->equals('cruser_id', $this->getBackendUser()->user['uid'])
            );
        }

        // Add matching criteria
        $query->matching(
            $query->logicalAnd(
                $query->equals('dataType', $dataType),
                $logicalOr
            )
        );

        // Set ordering
        $query->setOrderings(
            array('name' => QueryInterface::ORDER_ASCENDING)
        );

        return $query->execute();
    }

    /**
     * @param string $dataType
     * @return QueryResult
     */
    public function findForEveryone($dataType)
    {
        $query = $this->createQuery();

        // Add matching criteria
        $query->matching(
            $query->logicalAnd(
                $query->equals('dataType', $dataType),
                $query->equals('visibility', Selection::VISIBILITY_EVERYONE)
            )
        );

        // Set ordering
        $query->setOrderings(
            array('name' => QueryInterface::ORDER_ASCENDING)
        );

        return $query->execute();
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