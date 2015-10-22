<?php
namespace Fab\Vidi\Domain\Repository;

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

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use Fab\Vidi\Domain\Model\Selection;

/**
 * Repository for accessing Selections
 */
class SelectionRepository extends Repository {

	/**
	 * Initialize Repository
	 */
	public function initializeObject() {
		$querySettings = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * @param string $dataType
	 * @return QueryResult
	 */
	public function findByDataTypeForCurrentBackendUser($dataType) {
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
	public function findForEveryone($dataType) {
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
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

}