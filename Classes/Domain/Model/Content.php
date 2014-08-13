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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Vidi\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Converter\Field;
use TYPO3\CMS\Vidi\Converter\Property;
use TYPO3\CMS\Vidi\Service\FileReferenceService;
use TYPO3\CMS\Vidi\Tca\TcaService;

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
	 * Constructor for a Content object.
	 *
	 * @param string $dataType will basically correspond to a table name, e.g fe_users, tt_content, ...
	 * @param array $contentData
	 * @return \TYPO3\CMS\Vidi\Domain\Model\Content
	 */
	public function __construct($dataType, array $contentData = array()) {

		$this->dataType = $dataType;
		$this->uid = empty($contentData['uid']) ? NULL : $contentData['uid'];

		/** @var \TYPO3\CMS\Vidi\Tca\TableService $table */
		$table = TcaService::table($dataType);

		// Initialize the array containing the allowed fields to be filled-in.
		$fields = array('pid');

		// If a creation time stamp has been defined for this data type.
		if ($table->getTimeCreationField()) {
			$fields[] = $table->getTimeCreationField();
		}

		// If an update time stamp has been defined for this data type.
		if ($table->getTimeModificationField()) {
			$fields[] = $table->getTimeModificationField();
		}

		// Merge the other fields allowed for this data type.
		$fields = array_merge($fields, $table->getFields());

		// Fetch excluded fields from the grid.
		$excludedFields = TcaService::grid($this->dataType)->getExcludedFields();

		// Get column to be displayed
		foreach ($fields as $fieldName) {
			if (array_key_exists($fieldName, $contentData) && !in_array($fieldName, $excludedFields)) {
				$propertyName = Field::name($fieldName)->of($dataType)->toProperty();
				$this->$propertyName = $contentData[$fieldName];
			}
		}
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
	protected function hasRelation($propertyName) {
		$fieldName = Property::name($propertyName)->of($this)->toField();
		return $this->getTcaFieldService($fieldName)->hasRelation();
	}

	/**
	 * Return the TCA Table Service.
	 *
	 * @param string $fieldName
	 * @return \TYPO3\CMS\Vidi\Tca\FieldService
	 */
	protected function getTcaFieldService($fieldName) {
		return TcaService::table($this->dataType)->field($fieldName);
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
		$fieldName = Property::name($propertyName)->of($this)->toField();
		$foreignDataType = $this->getTcaFieldService($fieldName)->relationDataType();

		// Get the foreign repository instance form the factory
		/** @var \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository $foreignRepository */
		$foreignRepository = ContentRepositoryFactory::getInstance($foreignDataType);

		if ($this->getTcaFieldService($fieldName)->hasRelationWithCommaSeparatedValues()) {

			// Fetch values from repository
			$values = GeneralUtility::trimExplode(',', $this->$propertyName);
			$this->$propertyName = $foreignRepository->findIn('uid', $values);
		} elseif ($this->getTcaFieldService($fieldName)->hasRelationMany()) {

			$foreignFieldName = $this->getTcaFieldService($fieldName)->getForeignField();
			if (empty($foreignFieldName)) {
				$message = sprintf('Missing "foreign_field" key for field "%s" in table "%s".',
					$fieldName,
					$this->dataType
				);
				throw new \RuntimeException($message, 1376149186);
			}

			// Fetch values from repository.
			$foreignPropertyName = Field::name($foreignFieldName)->of($this)->toProperty();
			$findByProperty = 'findBy' . ucfirst($foreignPropertyName);

			// Date picker (type == group) are special fields because property path must contain the table name
			// to determine the relation type. Example for sys_category, property path will look like "items.sys_file"
			$propertyValue = $this->uid;
			$foreignTcaTableService = TcaService::table($foreignDataType);
			if ($foreignTcaTableService->field($foreignPropertyName)->isGroup()) {
				$propertyValue = $this->dataType . '.' . $this->uid;
			}

			$this->$propertyName = $foreignRepository->$findByProperty($propertyValue);

		} elseif ($this->getTcaFieldService($fieldName)->hasRelationOne()) {

			$fieldConfiguration = $this->getTcaFieldService($fieldName)->getConfiguration();

			// First case, we are on the "good side" of the relation, just query the repository
			if (empty($fieldConfiguration['foreign_field'])) {
				$this->$propertyName = $foreignRepository->findByUid($this->$propertyName);
			} else {
				// Second case, we are the "bad side" of the relation, query the foreign repository
				// e.g. in case of one-to-one relation.

				// We must query the opposite side to get the identifier of the foreign object.
				$foreignDataType = TcaService::table()->field($fieldName)->getForeignTable();
				$foreignField = TcaService::table()->field($fieldName)->getForeignField();
				$foreignRepository = ContentRepositoryFactory::getInstance($foreignDataType);
				$find = 'findOneBy' . GeneralUtility::underscoredToUpperCamelCase($foreignField);

				/** @var Content $foreignObject */
				$this->$propertyName = $foreignRepository->$find($this->getUid());
			}

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
		$offset = Field::name($offset)->of($this)->toProperty();
		return isset($this->$offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		$offset = Field::name($offset)->of($this)->toProperty();
		$getter = 'get' . ucfirst($offset);
		return $this->$getter();
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset
	 * @param mixed $value
	 * @return $this
	 */
	public function offsetSet($offset, $value) {
		$offset = Field::name($offset)->of($this)->toProperty();
		$setter = 'set' . ucfirst($offset);
		$this->$setter($value);
		return $this;
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset
	 * @throws NotImplementedException
	 * @return void
	 */
	public function offsetUnset($offset) {
		$message = 'Un-setting value for Array object is not supported';
		throw new NotImplementedException($message, 1376132306);
	}

	/**
	 * Convert this to array
	 *
	 * @return array
	 */
	public function toArray() {
		$result['uid'] = $this->uid;
		$propertiesAndValues = json_decode(json_encode($this), TRUE);

		foreach ($propertiesAndValues as $propertyName => $value) {
			$fieldName = Property::name($propertyName)->of($this)->toField();
			$result[$fieldName] = $value;
		}

		return $result;
	}

	/**
	 * Convert this object to an array containing the resolved values.
	 *
	 * @return array
	 */
	public function toValues() {
		$result['uid'] = $this->uid;
		$propertiesAndValues = json_decode(json_encode($this), TRUE);

		foreach ($propertiesAndValues as $propertyName => $value) {
			$fieldName = Property::name($propertyName)->of($this)->toField();

			$field = TcaService::table($this->dataType)->field($fieldName);
			$fieldType = $field->getType();
			if ($fieldType === TcaService::RADIO || $fieldType === TcaService::SELECT) {

				// Attempt to convert the value into a label for radio and select fields.
				$label = TcaService::table($this->getDataType())->field($fieldName)->getLabelForItem($value);
				if ($label) {
					$value = $label;
				}
			} elseif ($fieldType === TcaService::FILE) {

				if ($field->hasRelationMany()) {
					$files = FileReferenceService::getInstance()->findReferencedBy($propertyName, $this);

					$value = array();
					foreach ($files as $file) {
						$value[] = $file->getIdentifier();
					}
				} else {
					$files = FileReferenceService::getInstance()->findReferencedBy($propertyName, $this);
					if (!empty($files)) {
						$value = current($files)->getIdentifier();
					}
				}
			}
			$result[$fieldName] = $value;
		}

		return $result;
	}

	/**
	 * Return the properties of this object.
	 *
	 * @return array
	 */
	public function toProperties() {
		$result[] = 'uid';
		$propertiesAndValues = json_decode(json_encode($this), TRUE);

		foreach ($propertiesAndValues as $propertyName => $value) {
			$result[] = $propertyName;
		}
		return $result;
	}

	/**
	 * Return the properties of this object.
	 *
	 * @return array
	 */
	public function toFields() {
		$result[] = 'uid';
		$propertiesAndValues = json_decode(json_encode($this), TRUE);

		foreach ($propertiesAndValues as $propertyName => $value) {
			$result[] = Property::name($propertyName)->of($this)->toField();
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$labelField = TcaService::table($this->dataType)->getLabelField();
		return $this[$labelField];
	}

}
