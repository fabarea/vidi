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
 * Tells whether the field name is editable in the Grid.
 */
class IsEditableViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'The column name', true);
    }

    /**
     * Return whether field name is editable in the Grid.
     *
     * @return boolean
     */
    public function render()
    {
        return Tca::grid()->isEditable($this->arguments['name']);
    }

}
