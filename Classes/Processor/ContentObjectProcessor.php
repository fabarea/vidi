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
use TYPO3\CMS\Vidi\Behavior\SavingBehavior;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Signal\ProcessContentDataSignalArguments;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class for retrieving value from an object.
 * Non trivial case as the field name could contain a field path, e.g. metadata.title
 */
class ContentObjectProcessor implements SingletonInterface {

	/**
	 * @param ProcessContentDataSignalArguments $signalArguments
	 * @return array
	 */
	public function processRelations(ProcessContentDataSignalArguments $signalArguments) {

		$contentObject = $signalArguments->getContentObject();
		$fieldNameAndPath = $signalArguments->getFieldNameAndPath();
		$contentData = $signalArguments->getContentData();
		$savingBehavior = $signalArguments->getSavingBehavior();

		if ($savingBehavior !== SavingBehavior::REPLACE) {
			$contentData = $this->appendOrRemoveRelations($contentObject, $fieldNameAndPath, $contentData, $savingBehavior);
			$signalArguments->setContentData($contentData);
		}

		return array($signalArguments);
	}

	/**
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param $fieldNameAndPath
	 * @param array $contentData
	 * @param string $savingBehavior
	 * @return array
	 */
	protected function appendOrRemoveRelations(Content $object, $fieldNameAndPath, array $contentData, $savingBehavior) {

		foreach ($contentData as $fieldName => $values) {

			$resolvedObject = $this->getContentObjectResolver()->getObject($object, $fieldNameAndPath);

			if (TcaService::table($resolvedObject)->field($fieldName)->hasMany()) {

				// TRUE means CSV values must be converted to array.
				if (!is_array($values)) {
					$values = GeneralUtility::trimExplode(',', $values);
				}
				$relatedValues = $this->getRelatedValues($object, $fieldNameAndPath, $fieldName);

				foreach ($values as $value) {
					$appendOrRemove = $savingBehavior . 'Relations';
					$relatedValues = $this->$appendOrRemove($value, $relatedValues);
				}

				$contentData[$fieldName] = $relatedValues;
			}
		}
		return $contentData;
	}

	/**
	 * @param $value
	 * @param array $relatedValues
	 * @return array
	 */
	protected function appendRelations($value, array $relatedValues) {
		if (!in_array($value, $relatedValues)) {
			$relatedValues[] = $value;
		}
		return $relatedValues;
	}

	/**
	 * @param $value
	 * @param array $relatedValues
	 * @return array
	 */
	protected function removeRelations($value, array $relatedValues) {
		if (in_array($value, $relatedValues)) {
			$key = array_search($value, $relatedValues);
			unset($relatedValues[$key]);
		}
		return $relatedValues;
	}

	/**
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param string $fieldNameAndPath
	 * @param string $fieldName
	 * @return array
	 */
	protected function getRelatedValues(Content $object, $fieldNameAndPath, $fieldName) {

		$values = array();
		$relatedContentObjects = $this->getContentObjectResolver()->getValue($object, $fieldNameAndPath, $fieldName);

		if (is_array($relatedContentObjects)) {
			/** @var Content $relatedContentObject */
			foreach ($relatedContentObjects as $relatedContentObject) {
				$values[] = $relatedContentObject->getUid();
			}
		}

		return $values;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\ContentObjectResolver
	 */
	protected function getContentObjectResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\ContentObjectResolver');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}
}
