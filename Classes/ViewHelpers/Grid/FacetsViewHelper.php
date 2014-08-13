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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which returns the json serialization of the search fields.
 */
class FacetsViewHelper extends AbstractViewHelper {

	/**
	 * Returns the json serialization of the search fields.
	 *
	 * @return boolean
	 */
	public function render() {

		$facets = array();
		foreach (TcaService::grid()->getFacets() as $facetName) {
			$name = TcaService::grid()->facet($facetName)->getName();
			$facets[$name] = TcaService::grid()->facet($facetName)->getLabel();
		}

		return json_encode($facets);
	}

}
