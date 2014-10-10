<?php
namespace TYPO3\CMS\Vidi\Signal;

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

use TYPO3\CMS\Vidi\Domain\Model\Content;

/**
 * Class for storing arguments of a "post processing content data" signal.
 */
class ProcessContentDataSignalArguments {

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
	 * @var int
	 */
	protected $language;

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

	/**
	 * @return int
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param int $language
	 * @return $this
	 */
	public function setLanguage($language) {
		$this->language = $language;
		return $this;
	}


}
