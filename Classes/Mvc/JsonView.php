<?php
namespace TYPO3\CMS\Vidi\Mvc;

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

use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Install\Status\StatusInterface;
use TYPO3\CMS\Install\Status\Exception as StatusException;

/**
 * Simple JsonView (currently returns an associative array)
 */
class JsonView extends AbstractView {

	/**
	 * @var \TYPO3\CMS\Extbase\Mvc\Web\Response
	 */
	protected $response;

	/**
	 * @var JsonResult
	 */
	protected $result;

	/**
	 * @return string
	 */
	public function render() {
		# As of this writing, Json header is not automatically sent in the BE... even with json=format.
		$this->response->setHeader('Content-Type', 'application/json');
		$this->response->sendHeaders();

		return json_encode($this->result->toArray());
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\Web\Response $response
	 * @return void
	 */
	public function setResponse(Response $response) {
		$this->response = $response;
	}

	/**
	 * @param \TYPO3\CMS\Vidi\Mvc\JsonResult $result
	 * @return $this
	 */
	public function setResult(JsonResult $result) {
		$this->result = $result;
		return $this;
	}

}