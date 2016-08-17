<?php
namespace Fab\Vidi\Module;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for positioning components in a Vidi module.
 */
class ModulePosition extends Enumeration
{

    const DOC_HEADER = 'doc-header';

    const TOP = 'top';

    const BOTTOM = 'bottom';

    const LEFT = 'left';

    const RIGHT = 'right';

    const GRID = 'grid';

    const BUTTONS = 'buttons';

    const MENU_MASS_ACTION = 'menu-mass-action';

}