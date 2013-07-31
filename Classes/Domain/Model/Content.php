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
class Content {

	/**
	 * @var int
	 */
	protected $uid;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * Constructor for a Media object.
	 *
	 * @param array $contentData
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $storage
	 * @return \TYPO3\CMS\Vidi\Domain\Model\Content
	 */
	public function __construct(array $contentData = array(), $storage = NULL) {

		$this->uid = empty($contentData['uid']) ? NULL : $contentData['uid'];

		/** @var \TYPO3\CMS\Vidi\Tca\GridService $gridTcaService */
		$gridTcaService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService();

		// Get column to be displayed
		foreach ($gridTcaService->getFieldList() as $field) {
			if (isset($contentData[$field])) {
				$this->$field = $contentData[$field];
			}
		}

		// Not in Extbase context...
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
			$result = $this->$propertyName;
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
}
?>