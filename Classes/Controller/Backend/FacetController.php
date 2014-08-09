<?php
namespace TYPO3\CMS\Vidi\Controller\Backend;

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

/**
 * Controller which handles actions related to "Facet" in a Vidi module.
 */
class FacetController extends ActionController {

	/**
	 * Suggest values according to a facet. Output a json list of key / values.
	 *
	 * @param string $facet
	 * @param string $searchTerm
	 * @validate $facet TYPO3\CMS\Vidi\Domain\Validator\FacetValidator
	 * @return void
	 */
	public function suggestAction($facet, $searchTerm) {

		$values = $this->getFacetSuggestionService()->getSuggestions($facet);

		# Json header is not automatically sent in the BE...
		$this->response->setHeader('Content-Type', 'application/json');
		$this->response->sendHeaders();
		return json_encode($values, JSON_FORCE_OBJECT);
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Facet\FacetSuggestionService
	 */
	protected function getFacetSuggestionService () {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Facet\FacetSuggestionService', $this->settings);
	}
}
