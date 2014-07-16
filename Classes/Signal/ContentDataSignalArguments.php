<?php
namespace TYPO3\CMS\Vidi\Signal;
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
use TYPO3\CMS\Vidi\Domain\Model\Content;

/**
 * Class for storing arguments of a post-processing content data signal.
 */
class ContentDataSignalArguments {

	/**
	 * @var Content
	 */
	protected $contentObject;

	/**
	 * @var array
	 */
	protected $contentData;

	/**
	 * @var string
	 */
	protected $fieldNameAndPath;

	/**
	 * @var int
	 */
	protected $counter;

	/**
	 * @var int
	 */
	protected $savingBehavior;

	/**
	 * @param array $contentData
	 * @return $this
	 */
	public function setContentData($contentData) {
		$this->contentData = $contentData;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getContentData() {
		return $this->contentData;
	}

	/**
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $contentObject
	 * @return $this
	 */
	public function setContentObject($contentObject) {
		$this->contentObject = $contentObject;
		return $this;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Domain\Model\Content
	 */
	public function getContentObject() {
		return $this->contentObject;
	}

	/**
	 * @param string $fieldNameAndPath
	 * @return $this
	 */
	public function setFieldNameAndPath($fieldNameAndPath) {
		$this->fieldNameAndPath = $fieldNameAndPath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFieldNameAndPath() {
		return $this->fieldNameAndPath;
	}

	/**
	 * @param int $counter
	 * @return $this
	 */
	public function setCounter($counter) {
		$this->counter = $counter;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCounter() {
		return $this->counter;
	}

	/**
	 * @param int $savingBehavior
	 * @return $this
	 */
	public function setSavingBehavior($savingBehavior) {
		$this->savingBehavior = $savingBehavior;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSavingBehavior() {
		return $this->savingBehavior;
	}


}
