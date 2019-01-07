<?php
namespace Fab\Vidi\ViewHelpers\Grid\Column;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tca\Tca;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Tells whether the field name is visible in the Grid.
 */
class IsVisibleViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'The column name', true);
    }

    /**
     * Returns whether the column is visible.
     *
     * @return bool
     */
    public function render()
    {
        return Tca::grid()->isVisible($this->arguments['name']);
    }

}
