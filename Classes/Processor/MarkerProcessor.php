<?php
namespace TYPO3\CMS\Vidi\Processor;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Signal\ProcessContentDataSignalArguments;

/**
 * Marker Utility class for replacing "known" markers within an expression.
 */
class MarkerProcessor implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $wellKnownMarkers = array(
		'{*}',
		'{counter}',
		'{date}',
		'{creation_date}'
	);

	/**
	 * @param ProcessContentDataSignalArguments $signalArguments
	 * @return array
	 */
	public function processMarkers(ProcessContentDataSignalArguments $signalArguments) {

		$contentData = $signalArguments->getContentData();

		// Process markers
		foreach ($signalArguments->getContentData() as $fieldName => $value) {
			$currentValue = $this->getContentObjectResolver()->getValue($signalArguments->getContentObject(), $signalArguments->getFieldNameAndPath(), $fieldName);
			$creationTime = $this->getContentObjectResolver()->getValue($signalArguments->getContentObject(), $signalArguments->getFieldNameAndPath(), 'crdate');

			if (strpos($value, 's/') !== FALSE) {
				$contentData[$fieldName] = $this->searchAndReplace($value, $currentValue);
			} else {
				$contentData[$fieldName] = $this->replaceWellKnownMarkers($value, $currentValue, $signalArguments->getCounter(), $creationTime);
			}
		}
		$signalArguments->setContentData($contentData);

		return array($signalArguments);
	}

	/**
	 * @param string $value
	 * @param string $currentValue
	 * @param int $counter
	 * @param $creationTime
	 * @return string
	 */
	protected function replaceWellKnownMarkers($value, $currentValue, $counter, $creationTime) {

		// Replaces values.
		$replaces = array(
			$currentValue,
			$counter,
			date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']),
			date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $creationTime),
		);

		// Replace me!
		return str_replace($this->wellKnownMarkers, $replaces, $value);
	}

	/**
	 * @param string $value
	 * @param string $currentValue
	 * @return string
	 */
	protected function searchAndReplace($value, $currentValue) {

		$structure = explode('/', $value);
		$search = $structure[1];
		$replace = $structure[2];

		// Perhaps needs to be improved here if search contains "/" precisely.
		return preg_replace('/' . $search . '/isU', $replace, $currentValue);
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\ContentObjectResolver
	 */
	protected function getContentObjectResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\ContentObjectResolver');
	}

}
