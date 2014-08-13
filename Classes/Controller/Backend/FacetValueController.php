<?php
namespace TYPO3\CMS\Vidi\Controller\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
use TYPO3\CMS\Vidi\Domain\Repository\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Persistence\MatcherObjectFactory;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Controller which handles actions related to Vidi in the Backend.
 */
class FacetValueController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * List facet action for this controller. Output a json list of value
	 * corresponding of a searched facet.
	 * This action is expected to have a parameter format = json
	 *
	 * @param string $facet
	 * @param string $searchTerm
	 * @validate $facet TYPO3\CMS\Vidi\Domain\Validator\FacetValidator
	 * @return void
	 */
	public function listAction($facet, $searchTerm) {

		$values = array();

		if (TcaService::table()->field($facet)->hasRelation()) {

			// Fetch the adequate repository
			$foreignTable = TcaService::table()->field($facet)->getForeignTable();
			$contentRepository = ContentRepositoryFactory::getInstance($foreignTable);
			$tcaTableService = TcaService::table($foreignTable);

			// Initialize the matcher object.
			$matcher = MatcherObjectFactory::getInstance()->getMatcher(array(), $foreignTable);

			$numberOfValues = $contentRepository->countBy($matcher);
			if ($numberOfValues <= $this->getSuggestionLimit()) {

				$contents = $contentRepository->findBy($matcher);

				foreach ($contents as $content) {
					// Format content so that suggestion displays the uid on the Visual Search.
					#$values[] = array (
					#	'value' => $content['uid'],
					#	'label' => $content[$tcaTableService->getLabelField()],
					#);
					$values[] = $content[$tcaTableService->getLabelField()];
				}
			}
		} elseif (!TcaService::table()->field($facet)->isTextArea()) {

			// Fetch the adequate repository
			/** @var \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository $contentRepository */
			$contentRepository = ContentRepositoryFactory::getInstance();

			// Initialize some objects related to the query
			$matcher = MatcherObjectFactory::getInstance()->getMatcher();

			// Query the repository
			$contents = $contentRepository->findDistinctValues($facet, $matcher);

			// Only returns suggestion if it is not too much for the browser.
			if (count($contents) <= $this->getSuggestionLimit()) {
				foreach ($contents as $content) {
					$values[] = $content[$facet];
				}
			}
		}

		# Json header is not automatically respected in the BE with parameter format=json
		# so send one the hard way.
		header('Content-type: application/json');
		return json_encode($values);
	}

	/**
	 * Return from settings the suggestion limit.
	 *
	 * @return int
	 */
	public function getSuggestionLimit(){
		$suggestionLimit = (int) $this->settings['suggestionLimit'];
		if ($suggestionLimit <= 0) {
			$suggestionLimit = 1000;
		}
		return $suggestionLimit;
	}
}
