<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid;
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
 * View helper for rendering rows of contents
 */
class RowViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render rows of contents and output them in JSON formation
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @return string
	 */
	public function render(\TYPO3\CMS\Vidi\Domain\Model\Content $object) {

		$tcaGridService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService();

		// Initialize returned array
		$output = array();
		$output['DT_RowId'] = 'row-' . $object->getUid();
		$output['DT_RowClass'] = 'row-' . $object->getStatus();

		foreach($tcaGridService->getFields() as $fieldName => $configuration) {

			if ($tcaGridService->isNotSystem($fieldName)) {

				// Fetch value
				$getter = 'get' . ucfirst($fieldName);
				$value = $object->$getter();

				if ($tcaGridService->hasRenderer($fieldName)) {
					$renderer = $tcaGridService->getRenderer($fieldName);

					/** @var $rendererObject \TYPO3\CMS\Vidi\GridRenderer\GridRendererInterface */
					$rendererObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($renderer);
					$value = $rendererObject->render($object);
				}

				if (!empty($configuration['format'])) {
					$formatter = sprintf('TYPO3\CMS\Vidi\Formatter\%s::format', ucfirst($configuration['format']));
					$value = call_user_func($formatter, $value);
				}

				if (!empty($configuration['wrap'])) {
					$parts = explode('|', $configuration['wrap']);
					$value = implode($value, $parts);
				}
				$output[$fieldName] = $value;
			}
		}

		$output = json_encode($output);

		// remove curly bracket before and after since content is encapsulate with other content.
		return substr($output, 1, -1);
	}
}

?>