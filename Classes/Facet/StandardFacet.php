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
 * Class for configuring a custom Facet item.
 */
class StandardFacet implements FacetInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var array
	 */
	protected $suggestions;

	/**
	 * Constructor of a Generic Facet in Vidi.
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $suggestions
	 */
	public function __construct($name, $label = '', array $suggestions = array()) {
		$this->name = $name;
		if (empty($label)) {
			$label = $this->name;
		}
		$this->label = $label;
		$this->suggestions = $suggestions;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @return array
	 */
	public function getSuggestions() {
		return $this->suggestions;
	}

	/**
	 * Magic method implementation for retrieving state.
	 *
	 * @param array $states
	 * @return StandardFacet
	 */
	static public function __set_state($states) {
		return new StandardFacet($states['name'], $states['label'], $states['suggestions']);
	}
}
