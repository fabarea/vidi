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
use TYPO3\CMS\Vidi\Tca\TcaService;

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
		$creationTime = $this->getCreationTime($signalArguments);

		// Process markers
		foreach ($signalArguments->getContentData() as $fieldName => $updateValue) {
			if (is_scalar($updateValue)) {

				$currentValue = $this->getContentObjectResolver()->getValue(
					$signalArguments->getContentObject(),
					$signalArguments->getFieldNameAndPath(),
					$fieldName,
					$signalArguments->getLanguage()
				);
				$counter = $signalArguments->getCounter();

				$updateValue = $this->searchAndReplace($updateValue, $currentValue);
				$updateValue = $this->replaceWellKnownMarkers($updateValue, $currentValue, $counter, $creationTime);

				$contentData[$fieldName] = $updateValue;
			}
		}

		$signalArguments->setContentData($contentData);
		return array($signalArguments);
	}

	/**
	 * @param string $updateValue
	 * @param string $currentValue
	 * @param int $counter
	 * @param $creationTime
	 * @return string
	 */
	protected function replaceWellKnownMarkers($updateValue, $currentValue, $counter, $creationTime) {

		// Replaces values.
		$replaces = array(
			$currentValue,
			$counter,
			date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']),
			date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $creationTime),
		);

		// Replace me!
		return str_replace($this->wellKnownMarkers, $replaces, $updateValue);
	}

	/**
	 * @param string $updateValue
	 * @param string $currentValue
	 * @return string
	 */
	protected function searchAndReplace($updateValue, $currentValue) {

		if (strpos($updateValue, 's/') !== FALSE) {
			$structure = explode('/', $updateValue);
			$search = $structure[1];
			$replace = $structure[2];

			// Perhaps needs to be improved here if $search contains "/" precisely.
			$updateValue = preg_replace('/' . $search . '/isU', $replace, $currentValue);
		}
		return $updateValue;
	}

	/**
	 * @param ProcessContentDataSignalArguments $signalArguments
	 * @return int
	 */
	protected function getCreationTime(ProcessContentDataSignalArguments $signalArguments) {
		$creationTime = 0;
		$creationTimeField = TcaService::table($signalArguments->getContentObject()->getDataType())->getTimeCreationField();
		if ($creationTimeField) {
			$creationTime = $this->getContentObjectResolver()->getValue(
				$signalArguments->getContentObject(),
				$signalArguments->getFieldNameAndPath(),
				$creationTimeField
			);
		}
		return $creationTime;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\ContentObjectResolver
	 */
	protected function getContentObjectResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\ContentObjectResolver');
	}

}
