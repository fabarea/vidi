<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Content;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use TYPO3\CMS\Vidi\Domain\Model\Selection;
use TYPO3\CMS\Vidi\Domain\Repository\ContentRepositoryFactory;

/**
 * View helper which returns a list of records.
 */
class FindViewHelper extends AbstractContentViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();

		$this->registerArgument('orderings', 'array', 'Key / value array to be used for ordering. The key corresponds to a field name. The value can be "DESC" or "ASC".', FALSE, array());
		$this->registerArgument('limit', 'int', 'Limit the number of records being fetched.', FALSE, 0);
		$this->registerArgument('offset', 'int', 'Where to start the list of records.', FALSE, 0);
	}

	/**
	 * Fetch and returns a list of content objects.
	 *
	 * @return array
	 */
	public function render() {
		$selection = (int)$this->arguments['selection'];

		if ($selection > 0) {

			/** @var \TYPO3\CMS\Vidi\Domain\Repository\SelectionRepository $selectionRepository */
			$selectionRepository = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Repository\SelectionRepository');

			/** @var Selection $selection */
			$selection = $selectionRepository->findByUid($selection);
			$matches = json_decode($selection->getMatches(), TRUE);
			$dataType = $selection->getDataType();
		} else {
			$dataType = $this->arguments['dataType'];
			$matches = $this->arguments['matches'];
		}

		$orderings = $this->arguments['orderings'];
		$limit = $this->arguments['limit'];
		$offset = $this->arguments['offset'];
		$ignoreEnableFields = $this->arguments['ignoreEnableFields'];

		$querySignature = $this->getQuerySignature($dataType, $matches, $orderings, $limit, $offset);

		$resultSet = $this->getResultSetStorage()->get($querySignature);
		if (!$resultSet) {
			$matcher = $this->getMatcher($dataType, $matches);
			$orderings = $this->getOrder($dataType, $orderings);

			$this->emitPostProcessLimitSignal($dataType, $limit);
			$this->emitPostProcessOffsetSignal($dataType, $offset);

			$contentRepository = ContentRepositoryFactory::getInstance($dataType);
			$contentRepository->setDefaultQuerySettings($this->getDefaultQuerySettings($ignoreEnableFields));

			$resultSet = $contentRepository->findBy($matcher, $orderings, $limit, $offset);
			$this->getResultSetStorage()->set($querySignature, $resultSet); // store the result set for performance sake.
		}

		return $resultSet;
	}

}
