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
 * Return a possible column header.
 */
class HeaderViewHelper extends AbstractViewHelper
{

    /**
     * Returns a possible column header.
     *
     * @param string $name the column Name
     * @return boolean
     */
    public function render($name)
    {
        return Tca::grid()->getHeader($name);
    }

}
