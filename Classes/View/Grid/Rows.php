<?php

namespace Fab\Vidi\View\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper for rendering multiple rows.
 */
class Rows
{
    /**
     * Returns rows of content as array.
     *
     * @param array $objects
     * @param array $columns
     * @return array
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function render(array $objects = [], array $columns = array())
    {
        $rows = [];

        /** @var Row $row */
        $row = GeneralUtility::makeInstance(Row::class, $columns);
        foreach ($objects as $index => $object) {
            $rows[] = $row->render($object, $index);
        }

        return $rows;
    }
}
