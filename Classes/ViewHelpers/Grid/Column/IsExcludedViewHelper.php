<?php

namespace Fab\Vidi\ViewHelpers\Grid\Column;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * Tells whether the field name is excluded from the Grid.
 */
class IsExcludedViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'The column name', true);
    }

    /**
     * Returns whether the column is excluded from the Grid.
     *
     * @return bool
     */
    public function render()
    {
        $excludedFields = Tca::grid()->getExcludedFields();
        return !in_array($this->arguments['name'], $excludedFields, true);
    }
}
