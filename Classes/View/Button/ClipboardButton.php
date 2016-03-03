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
        $button = $this->makeLinkButton()
            ->setHref($this->getShowClipboardUri())
            ->setDataAttributes([
                'style' => $this->getClipboardService()->hasItems() ? '' : 'display: none;',
            ])
            ->setClasses('btn-clipboard-copy-or-move')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:clipboard.copy_or_move'))
            ->setIcon($this->getIconFactory()->getIcon('actions-document-paste-after', Icon::SIZE_SMALL))
            ->render();

        // Hack! No API for adding a style upon a button
        $button = str_replace('data-style', 'style', $button);

        $output = sprintf('<div style="float: left; margin-right: 3px">%s</div>', $button);
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
