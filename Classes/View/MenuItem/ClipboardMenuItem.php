<?php
namespace Fab\Vidi\View\MenuItem;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Media\Module\MediaModule;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View which renders a "mass delete" menu item to be placed in the grid menu.
 */
class ClipboardMenuItem extends AbstractComponentView
{

    /**
     * Renders a "mass delete" menu item to be placed in the grid menu.
     *
     * @return string
     */
    public function render()
    {
        $output = '';
        if ($this->getMediaModule()->hasFolderTree()) {
            $output = sprintf('<li><a href="%s" class="clipboard-save" >%s %s</a>',
                $this->getSaveInClipboardUri(),
                $this->getIconFactory()->getIcon('actions-document-paste-after', Icon::SIZE_SMALL),
                $this->getLanguageService()->sL('LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:save')
            );
        }
        return $output;
    }

    /**
     * @return string
     */
    protected function getSaveInClipboardUri()
    {
        $additionalParameters = array(
            $this->getModuleLoader()->getParameterPrefix() => array(
                'controller' => 'Clipboard',
                'action' => 'save',
                'format' => 'json',
            ),
        );
        return $this->getModuleLoader()->getModuleUrl($additionalParameters);
    }

    /**
     * @return MediaModule|object
     */
    protected function getMediaModule()
    {
        return GeneralUtility::makeInstance(MediaModule::class);
    }
}
