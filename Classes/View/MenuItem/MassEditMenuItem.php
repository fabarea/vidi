<?php
namespace Fab\Vidi\View\MenuItem;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\Icon;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View which renders a "mass edit" menu item to be placed in the grid menu.
 */
class MassEditMenuItem extends AbstractComponentView
{

    /**
     * Renders a "mass edit" menu item to be placed in the grid menu.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render()
    {
        return sprintf('<li><a href="#" class="dropdown-item mass-edit">%s %s (not implemented)</a></li>',
            $this->getIconFactory()->getIcon('actions-document-open', Icon::SIZE_SMALL),
            $this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf:edit')
        );
    }
}
