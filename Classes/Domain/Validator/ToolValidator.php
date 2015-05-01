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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Vidi\Tool\ToolRegistry;

/**
 * Validate the Tool class name before being instantiated.
 */
class ToolValidator extends AbstractValidator {

	/**
	 * Check whether $tool is valid.
	 *
	 * @param string $tool
	 * @return void
	 */
	public function isValid($tool) {

		$dataType = $this->getModuleLoader()->getDataType();
		$isValid = ToolRegistry::getInstance()->isAllowed($dataType, $tool);

		if (!$isValid) {
			$message = sprintf('This Tool "%s" is not allowed for the current data type.', $tool);
			$this->addError($message, 1409041510);
		}

		if (!class_exists($tool)) {
			$message = sprintf('I could not find class "%s"', $tool);
			$this->addError($message, 1409041511);
		}
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
