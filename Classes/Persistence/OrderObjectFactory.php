<?php
namespace Fab\Vidi\Persistence;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Tca\Tca;

/**
 * Factory class related to Order object.
 */
class OrderObjectFactory implements SingletonInterface
{

    /**
     * Gets a singleton instance of this class.
     *
     * @return \Fab\Vidi\Persistence\OrderObjectFactory
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Persistence\OrderObjectFactory');
    }

    /**
     * Returns an order object.
     *
     * @param string $dataType
     * @return \Fab\Vidi\Persistence\Order
     */
    public function getOrder($dataType = '')
    {

        // Default ordering
        $order = Tca::table($dataType)->getDefaultOrderings();

        // Retrieve a possible id of the column from the request
        $orderings = GeneralUtility::_GP('order');

        if (is_array($orderings) && isset($orderings[0])) {
            $columnPosition = $orderings[0]['column'];
            $direction = $orderings[0]['dir'];

            if ($columnPosition > 0) {
                $field = Tca::grid()->getFieldNameByPosition($columnPosition);

                $order = array(
                    $field => strtoupper($direction)
                );
            }
        }
        return GeneralUtility::makeInstance('Fab\Vidi\Persistence\Order', $order);
    }
}
