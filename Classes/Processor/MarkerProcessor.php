<?php
namespace TYPO3\CMS\Vidi\Processor;
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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Signal\ContentDataSignalArguments;

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
	 * @param ContentDataSignalArguments $signalArguments
	 * @return ContentDataSignalArguments
	 */
	public function processMarkers(ContentDataSignalArguments $signalArguments) {

		$contentData = $signalArguments->getContentData();

		// Process markers
		foreach ($signalArguments->getContentData() as $fieldName => $value) {
			$currentValue = $this->getContentObjectResolver()->getValue($signalArguments->getContentObject(), $signalArguments->getFieldNameAndPath(), $fieldName);
			$creationTime = $this->getContentObjectResolver()->getValue($signalArguments->getContentObject(), $signalArguments->getFieldNameAndPath(), 'crdate');
			$contentData[$fieldName] = $this->replaceWellKnownMarkers($value, $currentValue, $signalArguments->getCounter(), $creationTime);
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
	 * @return \TYPO3\CMS\Vidi\Resolver\ContentObjectResolver
	 */
	protected function getContentObjectResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\ContentObjectResolver');
	}

}
