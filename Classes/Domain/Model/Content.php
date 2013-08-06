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
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

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

		/** @var \TYPO3\CMS\Vidi\Tca\FieldService $fieldTcaService */
		$fieldTcaService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService();

		// Get column to be displayed
		foreach ($fieldTcaService->getFieldNames() as $field) {
			if (isset($contentData[$field])) {
				$this->$field = $contentData[$field];
			}
		}

		// Not automatically inherited like in Extbase context...
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
	}

	/**
	 * Magic getter.
	 *
	 * @param $property
	 * @return mixed
	 */
	public function __get($property) {
		return $this->$property;
	}

	/**
	 * Magic setter.
	 *
	 * @param $property
	 * @param $value
	 */
	public function __set($property, $value) {
		$this->$property = $value;
		return $this;
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
		if (substr($methodName, 0, 3) === 'get' && strlen($methodName) > 4) {
			$propertyName = strtolower(substr(substr($methodName, 3), 0, 1)) . substr(substr($methodName, 3), 1);

			/** @var \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository $contentRepository */
			$contentRepository = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Repository\ContentRepository');

			// Return content according relation type.
			$tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService();
			if ($tcaFieldService->hasRelationWithCommaSeparatedValues($propertyName)) {

				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->$propertyName);
				$dataType = $tcaFieldService->relationDataType($propertyName);

				$result = $contentRepository->setDataType($dataType)
					->findIn('uid', $values);

			} elseif ($tcaFieldService->hasRelationManyToMany($propertyName)) {
				// @todo implement me
				#$dataType = $tcaFieldService->relationDataType($propertyName);
				#$result = $contentRepository->setDataType($dataType)
				#	->findRelations($propertyName);
			} elseif ($tcaFieldService->hasRelationOneToMany($propertyName)) {
				// @todo implement me
				#$dataType = $tcaFieldService->relationDataType($propertyName);
				#$result = $contentRepository->setDataType($dataType)
				#	->findRelation($propertyName);
			} else {
				$result = $this->$propertyName;
			}

		} elseif (substr($methodName, 0, 3) === 'set' && strlen($methodName) > 4 && isset($arguments[0])) {
			$propertyName = strtolower(substr(substr($methodName, 3), 0, 1)) . substr(substr($methodName, 3), 1);
			$this->$propertyName = $arguments[0];
			$result = NULL;
		}
		return $result;
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
		$getter = 'get' . ucfirst($offset);
		return $this->$getter();
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->$offset = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->$offset);
	}

	/**
	 * Convert this to array
	 *
	 * @return array
	 */
	public function toArray() {
		$result['uid'] = $this->uid;
		$properties = json_decode(json_encode($this), true);
		return array_merge($result, $properties);
	}
}
?>