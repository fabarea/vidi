<?php
namespace Fab\Vidi\View\Button;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Utility\BackendUtility;
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
                        [],
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
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
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
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     */
    protected function getStoragePid()
    {
        if (GeneralUtility::_GP(Parameter::PID)) {
            $pid = GeneralUtility::_GP(Parameter::PID);
        } elseif ((int)Tca::table()->get('rootLevel') === 1) {
            $pid = 0;
        } else {
            // Get configuration from User TSConfig if any
            $tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->getModuleLoader()->getDataType());
            $tsConfig = $this->getBackendUser()->getTSConfig($tsConfigPath);
            $pid = $tsConfig['value'];

            // Get pid from Module Loader
            if (null === $pid) {
                $pid = $this->getModuleLoader()->getDefaultPid();
            }
        }
        return (int)$pid;
    }

}
