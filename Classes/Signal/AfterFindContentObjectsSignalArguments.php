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

use TYPO3\CMS\Vidi\Persistence\Matcher;

/**
 * Class for storing arguments of a "after find content objects" signal.
 */
class AfterFindContentObjectsSignalArguments {

	/**
	 * @var string
	 */
	protected $dataType;

	/**
	 * @var array
	 */
	protected $contentObjects;

	/**
	 * @var Matcher
	 */
	protected $matcher;

	/**
	 * @var int
	 */
	protected $limit;

	/**
	 * @var int
	 */
	protected $offset;

	/**
	 * @var bool
	 */
	protected $hasBeenProcessed;

	/**
	 * @var int
	 */
	protected $numberOfObjects = 0;

	/**
	 * @param array $contentObjects
	 * @return $this
	 */
	public function setContentObjects($contentObjects) {
		$this->contentObjects = $contentObjects;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getContentObjects() {
		return $this->contentObjects;
	}

	/**
	 * @param string $dataType
	 * @return $this
	 */
	public function setDataType($dataType) {
		$this->dataType = $dataType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * @param boolean $hasBeenProcessed
	 * @return $this
	 */
	public function setHasBeenProcessed($hasBeenProcessed) {
		$this->hasBeenProcessed = $hasBeenProcessed;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getHasBeenProcessed() {
		return $this->hasBeenProcessed;
	}

	/**
	 * @param int $limit
	 * @return $this
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @param \TYPO3\CMS\Vidi\Persistence\Matcher $matcher
	 * @return $this
	 */
	public function setMatcher($matcher) {
		$this->matcher = $matcher;
		return $this;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Persistence\Matcher
	 */
	public function getMatcher() {
		return $this->matcher;
	}

	/**
	 * @param int $numberOfObjects
	 * @return $this
	 */
	public function setNumberOfObjects($numberOfObjects) {
		$this->numberOfObjects = $numberOfObjects;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getNumberOfObjects() {
		return $this->numberOfObjects;
	}

	/**
	 * @param int $offset
	 * @return $this
	 */
	public function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}

}
