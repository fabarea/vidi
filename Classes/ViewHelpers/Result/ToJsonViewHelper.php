<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Result;

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
			'iTotalRecords' => $this->templateVariableContainer->get('numberOfObjects'),
			'iTotalDisplayRecords' => $this->templateVariableContainer->get('numberOfObjects'),
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
