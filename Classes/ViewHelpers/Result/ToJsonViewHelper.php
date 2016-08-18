<?php
namespace Fab\Vidi\ViewHelpers\Result;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\ViewHelpers\Grid\RowsViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for rendering a JSON response.
 */
class ToJsonViewHelper extends AbstractViewHelper
{

    /**
     * Render a Json response
     *
     * @return boolean
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
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
            'aaData' => $this->getRowsViewHelper()->render($objects, $columns),
        );

        $output = $this->encodeItems($output);
        $this->setHttpHeaders();
        return json_encode($output);
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
     * @param array $values
     * @return mixed
     */
    protected function encodeItems(array $values)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->encodeItems($value);
            } elseif (is_string($value)) {
                $values[$key] = utf8_encode($value);
            }
        }
        return $values;
    }

    /**
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setHttpHeaders()
    {
        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
        $response = $this->templateVariableContainer->get('response');
        $response->setHeader('Content-Type', 'application/json');
        $response->sendHeaders();
    }

    /**
     * @return RowsViewHelper
     */
    protected function getRowsViewHelper()
    {
        return $this->objectManager->get(RowsViewHelper::class);
    }
}
