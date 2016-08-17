<?php
namespace Fab\Vidi\ViewHelpers\Grid\Column;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * View helper for rendering a column title in the grid.
 */
class TitleViewHelper extends AbstractViewHelper
{

    /**
     * Returns a column title.
     *
     * @return string
     */
    public function render()
    {
        $columnName = $this->templateVariableContainer->get('columnName');
        return Tca::grid()->getLabel($columnName);
    }

}
