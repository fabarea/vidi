<?php
namespace TYPO3\CMS\Vidi\Persistence;

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

/**
 * Class for storing result set to improve performance.
 */
class ResultSetStorage implements SingletonInterface{

	/**
	 * @var array
	 */
	protected $resultSets = array();

	/**
	 * @param string $querySignature
	 * @return array
	 */
	public function get($querySignature) {
		$resultSet = NULL;
		if (isset($this->resultSets[$querySignature])) {
			$resultSet = $this->resultSets[$querySignature];
		}
		return $resultSet;
	}

	/**
	 * @param $querySignature
	 * @param array $resultSet
	 * @internal param array $resultSets
	 */
	public function set($querySignature, array $resultSet) {
		$this->resultSets[$querySignature] = $resultSet;
	}

}
