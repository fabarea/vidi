<?php
namespace Fab\Vidi\View\MenuItem;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\View\AbstractComponentView;

/**
 * View which renders a "divider" menu item to be placed in the grid menu.
 */
class DividerMenuItem extends AbstractComponentView
{

    /**
     * Renders a "divider" menu item to be placed in the grid menu.
     *
     * @return string
     */
    public function render()
    {
        return ' <li><hr class="dropdown-divider"></li>';
    }
}
