<?php
namespace TYPO3\CMS\Vidi\Resolver;

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
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class for retrieving value from an object.
 * Non trivial case as the field name could contain a field path, e.g. metadata.title
 */
class ContentObjectResolver implements SingletonInterface {

	/**
	 * @param Content $object
	 * @param string $fieldNameAndPath
	 * @return string
	 */
	public function getDataType(Content $object, $fieldNameAndPath) {

		// Important to notice the field name can contains a path, e.g. metadata.title and must be sanitized.
		$relationalFieldName = $this->getFieldPathResolver()->stripFieldName($fieldNameAndPath); // ex: metadata.title -> metadata

		// Handle case when field name leads to a relation.
		if ($object[$relationalFieldName] instanceof Content) {
			$resolvedDataType = $object[$relationalFieldName]->getDataType();
		} else {
			$resolvedDataType = $object->getDataType();
		}

		return $resolvedDataType;
	}

	/**
	 * Fetch the value of an object according to a field path.
	 * The returned value can be a string, int or array of Content objects.
	 *
	 * @param Content $object
	 * @param string $fieldNameAndPath
	 * @param string $fieldName
	 * @param int $language
	 * @return mixed
	 */
	public function getValue(Content $object, $fieldNameAndPath, $fieldName, $language = 0) {

		$resolvedContentObject = $this->getObject($object, $fieldNameAndPath);
		$resolvedValue = $resolvedContentObject[$fieldName];

		if (is_scalar($resolvedValue) && $language > 0) {
			$resolvedValue = $this->getLanguageService()->getLocalizedFieldName($resolvedContentObject, $language, $fieldName);
		}

		return $resolvedValue;
	}

	/**
	 * Fetch the value of an object according to a field name and path.
	 * The returned value is a Content object.
	 *
	 * @param Content $object
	 * @param string $fieldNameAndPath
	 * @return Content
	 */
	public function getObject(Content $object, $fieldNameAndPath) {

		// Important to notice the field name can contains a path, e.g. metadata.title and must be sanitized.
		$fieldPath = $this->getFieldPathResolver()->stripFieldName($fieldNameAndPath); // ex: metadata.title -> metadata

		// Handle case when field name leads to a relation.
		if ($object[$fieldPath] instanceof Content) {
			$resolvedObject = $object[$fieldPath];
		} else {
			$resolvedObject = $object;
		}

		return $resolvedObject;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Language\LanguageService
	 */
	protected function getLanguageService() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Language\LanguageService');
	}
}
