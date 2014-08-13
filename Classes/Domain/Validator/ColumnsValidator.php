<?php
namespace TYPO3\CMS\Vidi\Domain\Validator;

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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Validate "columns" to be displayed in the BE module.
 */
class ColumnsValidator extends AbstractValidator {

	/**
	 * Check if $columns is valid. If it is not valid, throw an exception.
	 *
	 * @param mixed $columns
	 * @return void
	 */
	public function isValid($columns) {

		foreach ($columns as $columnName) {
			if (TcaService::grid()->hasNotField($columnName)) {
				$message = sprintf('Column "%s" is not allowed. Actually, it was not configured to be displayed in the grid.', $columnName);
				$this->addError($message , 1380019718);
			}
		}
	}
}
