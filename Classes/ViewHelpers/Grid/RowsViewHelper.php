<?php
namespace Fab\Vidi\ViewHelpers\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\View\Grid\Row;

/**
 * View helper for rendering multiple rows.
 */
class RowsViewHelper extends AbstractViewHelper
{

    /**
     * Returns rows of content as array.
     *
     * @param array $objects
     * @param array $columns
     * @return string
     */
    public function render(array $objects = array(), array $columns = array())
    {
        $rows = array();

        /** @var Row $row */
        $row = GeneralUtility::makeInstance('Fab\Vidi\View\Grid\Row', $columns);
        foreach ($objects as $index => $object) {
            $rows[] = $row->render($object, $index);
        }

        return $rows;
    }
}
