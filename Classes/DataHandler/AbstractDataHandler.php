<?php
namespace TYPO3\CMS\Vidi\DataHandler;

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
 * Abstract class for Data Handler in the context of Vidi.
 */
abstract class AbstractDataHandler implements DataHandlerInterface, SingletonInterface {

	/**
	 * @var array
	 */
	protected $errorMessages;

	/**
	 * Return error that have occurred while processing the data.
	 *
	 * @return array
	 */
	public function getErrorMessages() {
		return $this->errorMessages;
	}

}
