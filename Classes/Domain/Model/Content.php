<?php
namespace TYPO3\CMS\Vidi\Domain\Model;

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

/**
 * Content representation.
 */
class Content implements \ArrayAccess {

	/**
	 * @var int
	 */
	protected $uid;

	/**
	 * @var string
	 */
	protected $dataType;

	/**
	 * @var \TYPO3\CMS\Vidi\Tca\FieldService
	 */
	protected $tcaFieldService;

	/**
	 * Constructor for a Content object.
	 *
	 * @param string $dataType will basically correspond to a table name, e.g fe_users, tt_content, ...
	 * @param array $contentData
	 * @return \TYPO3\CMS\Vidi\Domain\Model\Content
	 */
	public function __construct($dataType, array $contentData = array()) {

		$this->dataType = $dataType;
		$this->uid = empty($contentData['uid']) ? NULL : $contentData['uid'];

		$this->tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($dataType);
		$fields = $this->tcaFieldService->getFieldNames();

		/** @var \TYPO3\CMS\Vidi\Tca\TableService $tcaTableService */
		$tcaTableService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getTableService($dataType);

		// Create time stamp field
		if ($tcaTableService->getTimeCreationField()) {
			$fields[] = $tcaTableService->getTimeCreationField();
		}

		// Update time stamp field
		if ($tcaTableService->getTimeModificationField()) {
			$fields[] = $tcaTableService->getTimeModificationField();
		}

		// Get column to be displayed
		foreach ($fields as $fieldName) {
			if (isset($contentData[$fieldName])) {
				$propertyName = $this->convertFieldNameToPropertyName($fieldName);
				$this->$propertyName = $contentData[$fieldName];
			}
		}
	}

	/**
	 * Convert a field name to a property name.
	 * Example: converts blog_example to blogExample
	 *
	 * @param $fieldName
	 * @return string
	 */
	protected function convertFieldNameToPropertyName($fieldName){
		return \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($fieldName);
	}

	/**
	 * Convert a property name to a field name.
	 * Example: converts blogExample to blog_example
	 *
	 * @param $propertyName
	 * @return string
	 */
	protected function convertPropertyNameToFieldName($propertyName) {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
	}

	/**
	 * Dispatches magic methods (findBy[Property]())
	 *
	 * @param string $methodName The name of the magic method
	 * @param string $arguments The arguments of the magic method
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException
	 * @return mixed
	 * @api
	 */
	public function __call($methodName, $arguments) {
		$result = NULL;
		if (substr($methodName, 0, 3) === 'get' && strlen($methodName) > 4) {
			$propertyName = strtolower(substr(substr($methodName, 3), 0, 1)) . substr(substr($methodName, 3), 1);

			$result = $this->$propertyName;

			// TRUE means it is a relation and it is not yet resolved.
			if ($this->hasRelation($propertyName) && is_scalar($this->$propertyName)) {
				$result = $this->resolveRelation($propertyName);
			}

		} elseif (substr($methodName, 0, 3) === 'set' && strlen($methodName) > 4 && isset($arguments[0])) {
			$propertyName = strtolower(substr(substr($methodName, 3), 0, 1)) . substr(substr($methodName, 3), 1);
			$this->$propertyName = $arguments[0];
		}
		return $result;
	}

	/**
	 * Tell whether the property has a relation.
	 *
	 * @param string $propertyName
	 * @return bool
	 */
	protected function hasRelation($propertyName){
		$fieldName = $this->convertPropertyNameToFieldName($propertyName);
		return $this->tcaFieldService->hasRelation($fieldName);
	}

	/**
	 * Try to "resolve" the property whether it has a relation.
	 * If the property has not relation it simply returns the same value.
	 *
	 * @throws \RuntimeException
	 * @param string $propertyName
	 * @return mixed
	 */
	protected function resolveRelation($propertyName) {

		// Convert property name to field name and get the foreign data type.
		$fieldName = $this->convertPropertyNameToFieldName($propertyName);
		$foreignDataType = $this->tcaFieldService->relationDataType($fieldName);

		// Get the foreign repository instance form the factory
		/** @var \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository $foreignRepository */
		$foreignRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance($foreignDataType);

		if ($this->tcaFieldService->hasRelationWithCommaSeparatedValues($fieldName)) {

			// Fetch values from repository
			$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->$propertyName);
			$this->$propertyName = $foreignRepository->findIn('uid', $values);
		} elseif ($this->tcaFieldService->hasRelationMany($fieldName)) {

			$foreignFieldName = $this->tcaFieldService->getForeignField($fieldName);
			if (empty($foreignFieldName)) {
				$message = sprintf('Missing "foreign_field" key for field "%s" in table "%s".',
					$fieldName,
					$this->dataType
				);
				throw new \RuntimeException($message, 1376149186);
			}

			// Fetch values from repository.
			$foreignPropertyName = $this->convertFieldNameToPropertyName($foreignFieldName);
			$findByProperty = 'findBy' . ucfirst($foreignPropertyName);
			$this->$propertyName = $foreignRepository->$findByProperty($this->uid);

		} elseif ($this->tcaFieldService->hasRelationOne($propertyName)) {

			// Fetch value from repository
			$this->$propertyName = $foreignRepository->findByUid($this->$propertyName);
		}
		return $this->$propertyName;
	}

	/**
	 * @return int
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		$offset = $this->convertFieldNameToPropertyName($offset);
		return isset($this->$offset);
	}

	/**
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		$offset = $this->convertFieldNameToPropertyName($offset);
		$getter = 'get' . ucfirst($offset);
		return $this->$getter();
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset
	 * @param mixed $value
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$message = 'Setting value for Array object is not supported';
		throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException($message, 1376132305);
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
	 * @return void
	 */
	public function offsetUnset($offset) {
		$message = 'Un-setting value for Array object is not supported';
		throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException($message, 1376132306);
	}

	/**
	 * Convert this to array
	 *
	 * @return array
	 */
	public function toArray() {
		$result['uid'] = $this->uid;
		$properties = json_decode(json_encode($this), true);

		foreach ($properties as $propertyName => $value) {
			$fieldName = $this->convertPropertyNameToFieldName($propertyName);
			$result[$fieldName] = $value;
		}

		return $result;
	}
}
?>