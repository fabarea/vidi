<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Content;

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
			$dataType = $this->arguments['type'];
			if (!empty($this->arguments['dataType'])) {
				print 'Sorry to be so rude! There is something to change in the View Helper "v:find". Please replace attribute "dataType" by "type". This is a shorter syntax...';
				exit();
			}
			$matches = $this->replacesAliases($this->arguments['matches']);
		}

		$orderings = $this->replacesAliases($this->arguments['orderings']);
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
