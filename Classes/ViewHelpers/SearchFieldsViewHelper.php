<?php
namespace TYPO3\CMS\Vidi\ViewHelpers;
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
/**
 * View helper which returns the json serialization of the search fields.
 */
class SearchFieldsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Returns the json serialization of the search fields.
	 *
	 * @return boolean
	 */
	public function render() {
		$tcaGridService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService();
		$tcaTableService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getTableService();
		$tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService();
		$searchFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $tcaTableService->getSearchFields());

		$labels = array();
		foreach ($tcaGridService->getFacets() as $fieldName => $configuration) {
			if (is_int($fieldName)) {
				$fieldName = $configuration;
			}
			// @todo keep no label field for now.
			#$label = $tcaFieldService->getLabel($field);
			#$labels[] = str_replace(':', '', $label);
			$labels[] = $fieldName;
		}
		return json_encode($labels);
	}
}

?>