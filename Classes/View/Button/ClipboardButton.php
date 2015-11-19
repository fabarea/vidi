<?php
namespace Fab\Vidi\View\Button;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\View\AbstractComponentView;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * View which renders a "clipboard" button to be placed in the doc header.
 */
class ClipboardButton extends AbstractComponentView
{

    /**
     * Renders a "clipboard" button to be placed in the doc header.
     *
     * @return string
     */
    public function render()
    {
        $output = sprintf('<div style="float: left;"><a style="%s" href="%s" title="%s" class="btn-clipboard-copy-or-move">%s</a></div>',
            $this->getClipboardService()->hasItems() ? '' : 'display: none;',
            $this->getShowClipboardUri(),
            LocalizationUtility::translate('clipboard.copy_or_move', 'vidi'),
            $this->getIconFactory()->getIcon('actions-document-paste-after', Icon::SIZE_SMALL)
        );
        return $output;
    }

    /**
     * @return string
     */
    protected function getShowClipboardUri()
    {
        $additionalParameters = array(
            $this->getModuleLoader()->getParameterPrefix() => array(
                'controller' => 'Clipboard',
                'action' => 'show',
            ),
        );
        return $this->getModuleLoader()->getModuleUrl($additionalParameters);
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Service\ClipboardService
     */
    protected function getClipboardService()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Service\ClipboardService');
    }

}
