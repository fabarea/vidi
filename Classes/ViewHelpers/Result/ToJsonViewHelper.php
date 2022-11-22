<?php

namespace Fab\Vidi\ViewHelpers\Result;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Http\Response;
use Fab\Vidi\View\Grid\Rows;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper for rendering a JSON response.
 */
class ToJsonViewHelper extends AbstractResultViewHelper
{
    /**
     * Render a Json response
     */
    public function render()
    {
        $objects = $this->templateVariableContainer->get('objects');
        $columns = $this->templateVariableContainer->get('columns');
        $output = array(
            'sEcho' => $this->getNextTransactionId(),
            'iTotalRecords' => $this->templateVariableContainer->get('numberOfObjects'),
            'iTotalDisplayRecords' => $this->templateVariableContainer->get('numberOfObjects'),
            'iNumberOfRecords' => count($objects),
            'aaData' => $this->getRowsView()->render($objects, $columns),
        );

        $this->sendRepsonse($this->getJsonResponse(json_encode($output)));
    }

    /**
     * @return int
     */
    protected function getNextTransactionId()
    {
        $transaction = 0;
        if (GeneralUtility::_GET('sEcho')) {
            $transaction = (int)GeneralUtility::_GET('sEcho') + 1;
        }
        return $transaction;
    }

    /**
     * @return Response
     */
    protected function getJsonResponse(string $json)
    {
        $streamFactory = GeneralUtility::makeInstance(StreamFactoryInterface::class);

        // @todo Implement file name generation from objects and timestamp
        return $this->getResponse('download')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * @return Rows|object
     */
    protected function getRowsView()
    {
        return GeneralUtility::makeInstance(Rows::class);
    }
}
