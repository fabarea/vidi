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
 * Return a possible column header.
 */
class HeaderViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'The column name', true);
    }

    /**
     * Returns a possible column header.
     *
     * @return boolean
     */
    public function render()
    {
        return Tca::grid()->getHeader($this->arguments['name']);
    }

}
