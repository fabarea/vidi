<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid\Column;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Tca\TcaService;

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
