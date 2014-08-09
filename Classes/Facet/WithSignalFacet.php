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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class for telling the Facet will handle a signal.
 * Useful to tell apart "standard" Facet from "withSignal".
 */
class WithSignalFacet extends StandardFacet {

	/**
	 * Constructor of a Facet with signal handler.
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $suggestions
	 */
	public function __construct($name, $label, array $suggestions = array()) {
		parent::__construct($name, $label, $suggestions);
	}
}
