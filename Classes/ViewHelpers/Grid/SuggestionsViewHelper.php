<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid;

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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which returns suggestion for the Visual Search bar.
 */
class SuggestionsViewHelper extends AbstractViewHelper {

	/**
	 * Returns the json serialization of the search fields.
	 *
	 * @return boolean
	 */
	public function render() {

		$suggestions = array();
		foreach (TcaService::grid()->getFacets() as $facet) {
			$name = TcaService::grid()->facet($facet)->getName();
			$suggestions[$name] = $this->getFacetSuggestionService()->getSuggestions($name);
		}

		return json_encode($suggestions, JSON_FORCE_OBJECT);
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Facet\FacetSuggestionService
	 */
	protected function getFacetSuggestionService () {
		$settings = $this->templateVariableContainer->get('settings');
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Facet\FacetSuggestionService', $settings);
	}

}
