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
 * Tells whether the column can be hidden or not.
 */
class CanBeHiddenViewHelper extends AbstractViewHelper
{

    /**
     * Returns whether the column can be hidden or not.
     *
     * @param string $name the column Name
     * @return boolean
     */
    public function render($name)
    {
        return Tca::grid()->canBeHidden($name);
    }

}
