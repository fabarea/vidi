<?php
namespace TYPO3\CMS\Vidi\Tca;

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
use TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException;
use TYPO3\CMS\Vidi\Facet\FacetInterface;

/**
 * A class to handle TCA grid configuration
 */
class FacetService implements TcaServiceInterface {

	/**
	 * @var FacetInterface
	 */
	protected $facet;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * Constructor
	 *
	 * @param FacetInterface $facet
	 * @param string $tableName
	 * @throws \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException
	 * @return \TYPO3\CMS\Vidi\Tca\FacetService
	 */
	public function __construct($facet, $tableName) {

		$this->facet = $facet;
		$this->tableName = $tableName;

		if (empty($GLOBALS['TCA'][$this->tableName])) {
			throw new InvalidKeyInArrayException('No TCA existence for table name: ' . $this->tableName, 1356945108);
		}

		$this->tca = $GLOBALS['TCA'][$this->tableName]['grid'];
	}

	/**
	 * Return a custom "key" of the facet or the facet itself if key is not defined.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key = '') {
		if (empty($key)) {
			$result = $this->facet;
		} else {
			$getter = 'get' . ucfirst($key);
			$result = $this->facet->$getter();
		}
		return $result;
	}

	/**
	 * Return the facet "key".
	 *
	 * @return string
	 */
	public function getName() {
		return $this->facet->getName();
	}

	/**
	 * Return the facet "label".
	 *
	 * @return string
	 */
	public function getLabel() {

		if ($this->facet->getLabel() === $this->facet->getName()) {
			$label = TcaService::table($this->tableName)->field($this->facet->getName())->getLabel();
		} else {
			$label = LocalizationUtility::translate($this->facet->getLabel(), '');
			if (empty($label)) {
				$label = $this->facet->getLabel();
			}
		}

		return $label;
	}

	/**
	 * Tell whether the facet has suggestions.
	 *
	 * @return string
	 */
	public function hasSuggestions() {
		$suggestions = $this->facet->getSuggestions();
		return !empty($suggestions);
	}

	/**
	 * Return the suggestion of the facet".
	 *
	 * @return array
	 */
	public function getSuggestions() {
		$values = array();
		foreach ($this->facet->getSuggestions() as $key => $label) {
			$localizedLabel = LocalizationUtility::translate($label, '');
			if (!empty($localizedLabel)) {
				$label = $localizedLabel;
			}
			$values[$key] = $label;
		}
		return $values;
	}

	/**
	 * Return the facet "callBack".
	 *
	 * @return string
	 */
	public function getCallBack() {
		return $this->facet->getCallBackClassName();
	}
}
