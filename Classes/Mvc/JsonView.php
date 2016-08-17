<?php
namespace Fab\Vidi\Mvc;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Install\Status\StatusInterface;
use TYPO3\CMS\Install\Status\Exception as StatusException;

/**
 * Simple JsonView (currently returns an associative array)
 */
class JsonView extends AbstractView
{

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
    public function render()
    {
        # As of this writing, Json header is not automatically sent in the BE... even with json=format.
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->sendHeaders();

        return json_encode($this->result->toArray());
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Response $response
     * @return void
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @param \Fab\Vidi\Mvc\JsonResult $result
     * @return $this
     */
    public function setResult(JsonResult $result)
    {
        $this->result = $result;
        return $this;
    }

}