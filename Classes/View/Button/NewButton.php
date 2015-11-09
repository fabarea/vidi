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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Module\Parameter;
use Fab\Vidi\Tca\Tca;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View which renders a "new" button to be placed in the doc header.
 */
class NewButton extends AbstractComponentView
{

    /**
     * Renders a "new" button to be placed in the doc header.
     *
     * @return string
     */
    public function render()
    {

        // General New button
        if ($this->getModuleLoader()->copeWithPageTree()) {

            // Wizard "new"
            $buttons[] = $this->makeLinkButton()
                ->setHref($this->getNewUri())
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:newRecordGeneral'))
                ->setIcon($this->getIconFactory()->getIcon('actions-document-new', Icon::SIZE_SMALL))
                ->render();

            $buttons[] = $this->makeLinkButton()
                ->setHref($this->getNewUri())
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:newRecordGeneral'))
                ->setIcon(
                    $this->getIconFactory()->getIconForRecord(
                        $this->getModuleLoader()->getDataType(),
                        array(),
                        Icon::SIZE_SMALL
                    )
                )
                ->render();

            $output = '<div class="btn-toolbar" role="toolbar" aria-label="">' . implode("\n", $buttons) . '</div>';

        } else {

            // New button only for the current data type.
            $output = $this->makeLinkButton()->setHref($this->getNewUri())
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:newRecordGeneral'))
                ->setIcon($this->getIconFactory()->getIcon('actions-document-new', Icon::SIZE_SMALL))
                ->render();
        }


        return $output;
    }

    /**
     * Render a create URI given a data type.
     *
     * @return string
     */
    protected function getUriWizardNew()
    {
        // Return URL in any case.
        $arguments['returnUrl'] = $this->getModuleLoader()->getModuleUrl();

        // Add possible id parameter
        if (GeneralUtility::_GP(Parameter::PID)) {
            $arguments['id'] = GeneralUtility::_GP(Parameter::PID);
        }

        $uri = BackendUtility::getModuleUrl(
            'db_new',
            $arguments
        );

        return $uri;
    }

    /**
     * Render a create URI given a data type.
     *
     * @return string
     */
    protected function getNewUri()
    {
        $uri = BackendUtility::getModuleUrl(
            'record_edit',
            array(
                $this->getNewParameterName() => 'new',
                'returnUrl' => $this->getModuleLoader()->getModuleUrl()
            )
        );
        return $uri;
    }

    /**
     * @return string
     */
    protected function getNewParameterName()
    {
        return sprintf(
            'edit[%s][%s]',
            $this->getModuleLoader()->getDataType(),
            $this->getStoragePid()
        );
    }

    /**
     * Return the default configured pid.
     *
     * @return int
     */
    protected function getStoragePid()
    {
        if (GeneralUtility::_GP(Parameter::PID)) {
            $pid = GeneralUtility::_GP(Parameter::PID);
        } elseif (Tca::table()->get('rootLevel')) {
            $pid = 0;
        } else {
            // Get configuration from User TSconfig if any
            $tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->getModuleLoader()->getDataType());
            $result = $this->getBackendUser()->getTSConfig($tsConfigPath);
            $pid = $result['value'];

            // Get pid from Module Loader
            if (NULL === $pid) {
                $pid = $this->getModuleLoader()->getDefaultPid();
            }
        }
        return $pid;
    }

}
