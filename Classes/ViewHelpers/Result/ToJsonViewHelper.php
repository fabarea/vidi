<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Result;
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for rendering a JSON response.
 */
class ToJsonViewHelper extends AbstractViewHelper {

	/**
	 * Render a Json response
	 *
	 * @return boolean
	 */
	public function render() {

		$objects = $this->templateVariableContainer->get('objects');
		$columns = $this->templateVariableContainer->get('columns');
		$output = array(
			'sEcho' => $this->getNextTransactionId(),
			'iTotalRecords' => $this->templateVariableContainer->get('numberOfContents'),
			'iTotalDisplayRecords' => $this->templateVariableContainer->get('numberOfContents'),
			'iNumberOfRecords' => count($objects),
			'aaData' => $this->getRowsViewHelper()->render($objects, $columns),
		);

		$this->setHttpHeaders();
		return json_encode($output);
	}

	/**
	 * @return int
	 */
	protected function getNextTransactionId() {
		$transaction = 0;
		if (GeneralUtility::_GET('sEcho')) {
			$transaction = (int)GeneralUtility::_GET('sEcho') + 1;
		}
		return $transaction;
	}

	/**
	 * @return void
	 */
	protected function setHttpHeaders() {
		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
		$response = $this->templateVariableContainer->get('response');
		$response->setHeader('Content-Type', 'application/json');
		$response->sendHeaders();
	}

	/**
	 * @return \TYPO3\CMS\Vidi\ViewHelpers\Grid\RowsViewHelper
	 */
	protected function getRowsViewHelper() {
		return $this->objectManager->get('TYPO3\CMS\Vidi\ViewHelpers\Grid\RowsViewHelper');
	}
}
