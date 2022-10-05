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
 * View which renders a "mass delete" menu item to be placed in the grid menu.
 */
class MassDeleteMenuItem extends AbstractComponentView
{
    /**
     * Renders a "mass delete" menu item to be placed in the grid menu.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render()
    {
        return sprintf(
            '<li><a href="%s" class="dropdown-item mass-delete" >%s %s</a>',
            $this->getMassDeleteUri(),
            $this->getIconFactory()->getIcon('actions-edit-delete', Icon::SIZE_SMALL),
            $this->getLanguageService()->sL('LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:delete')
        );
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getMassDeleteUri()
    {
        $additionalParameters = array(
            $this->getModuleLoader()->getParameterPrefix() => array(
                'controller' => 'Content',
                'action' => 'delete',
                'format' => 'json',
            ),
        );
        return $this->getModuleLoader()->getModuleUrl($additionalParameters);
    }
}
