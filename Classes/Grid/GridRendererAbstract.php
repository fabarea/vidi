<?php
namespace TYPO3\CMS\Vidi\Grid;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class rendering relation
 */
abstract class GridRendererAbstract implements GridRendererInterface {

	/**
	 * The content object.
	 *
	 * @var \TYPO3\CMS\Vidi\Domain\Model\Content
	 */
	 protected $object;

	/**
	 * @var string
	 */
	protected $fieldName;

	/**
	 * @var int
	 */
	protected $rowIndex;

	/**
	 * @var array
	 */
	protected $fieldConfiguration = array();

	/**
	 * @var array
	 */
	protected $gridRendererConfiguration = array();

	/**
	 * @return \TYPO3\CMS\Vidi\Domain\Model\Content
	 */
	public function getObject() {
		return $this->object;
	}

	/**
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @return $this
	 */
	public function setObject($object) {
		$this->object = $object;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFieldName() {
		return $this->fieldName;
	}

	/**
	 * @param string $fieldName
	 * @return $this
	 */
	public function setFieldName($fieldName) {
		$this->fieldName = $fieldName;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getRowIndex() {
		return $this->rowIndex;
	}

	/**
	 * @param int $rowIndex
	 * @return $this
	 */
	public function setRowIndex($rowIndex) {
		$this->rowIndex = $rowIndex;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getFieldConfiguration() {
		return $this->fieldConfiguration;
	}

	/**
	 * @param array $fieldConfiguration
	 * @return $this
	 */
	public function setFieldConfiguration($fieldConfiguration) {
		$this->fieldConfiguration = $fieldConfiguration;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getGridRendererConfiguration() {
		return $this->gridRendererConfiguration;
	}

	/**
	 * @param array $gridRendererConfiguration
	 * @return $this
	 */
	public function setGridRendererConfiguration($gridRendererConfiguration) {
		$this->gridRendererConfiguration = $gridRendererConfiguration;
		return $this;
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}
}
