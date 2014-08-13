<?php
namespace TYPO3\CMS\Vidi\Facet;

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

/**
 * Interface dealing with Facet for the Visual Search bar.
 */
interface FacetInterface {

	/**
	 * Return the "key" of the facet.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Return the "label" of the facet.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Return possible "suggestions" of the facet.
	 *
	 * @return array
	 */
	public function getSuggestions();

}
