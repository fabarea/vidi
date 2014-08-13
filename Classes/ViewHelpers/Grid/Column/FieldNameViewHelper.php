<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid\Column;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Computes the final field name in the context of the Grid.
 */
class FieldNameViewHelper extends AbstractViewHelper {

	/**
	 * Return the final field name in the context of the Grid.
	 *
	 * @return string
	 */
	public function render() {

		$fieldName = $this->templateVariableContainer->get('columnName');
		$configuration = $this->templateVariableContainer->get('configuration');

		if (isset($configuration['dataType'])) {
			$fieldName = $configuration['dataType'] . '.' . $fieldName;
		}

		return $fieldName;
	}

}
