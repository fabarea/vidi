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
 * Validate "facet" to be used in the repository.
 */
class FacetValidator extends AbstractValidator {

	/**
	 * Check if $facet is valid. If it is not valid, throw an exception.
	 *
	 * @param mixed $facet
	 * @return void
	 */
	public function isValid($facet) {

		if (! TcaService::grid()->hasFacet($facet)) {
			$message = sprintf('Facet "%s" is not allowed. Actually, it was not configured to be displayed in the grid.', $facet);
			$this->addError($message, 1380019719);
		}
	}
}
