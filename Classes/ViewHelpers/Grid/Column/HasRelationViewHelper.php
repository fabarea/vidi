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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Tells whether the current field name has a relation to the main content (given by the Module Loader implicitly).
 */
class HasRelationViewHelper extends AbstractViewHelper {

	/**
	 * Return whether the current field name has a relation to the main content.
	 *
	 * @return boolean
	 */
	public function render() {
		$fieldNameAndPath = $this->templateVariableContainer->get('columnName');
		$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
		$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);
		$hasRelation = TcaService::table($dataType)->field($fieldName)->hasRelation();
		return $hasRelation;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}
}
