<?php
namespace Fab\Vidi\View\Tab;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Module\Parameter;
use Fab\Vidi\Module\ModuleService;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View component which renders a data type menu for the List2 module.
 */
class DataTypeTab extends AbstractComponentView
{

    /**
     * Renders a "new" button to be placed in the doc header.
     *
     * @return string
     */
    public function render()
    {
        $output = ''; // Initialize variable as string.
        if ($this->getModuleLoader()->copeWithPageTree()) {
            $moduleCodes = ModuleService::getInstance()->getModulesForCurrentPid();
            $output = $this->assembleDataTypeTab($moduleCodes);
        }
        return $output;
    }

    /**
     * @param array $moduleCodes
     * @return string
     */
    protected function assembleDataTypeTab(array $moduleCodes)
    {
        return sprintf('<ul class="nav nav-tabs">%s</ul>',
            $this->assembleTab($moduleCodes)
        );
    }

    /**
     * @return string
     */
    protected function getModuleToken()
    {
        $moduleName = GeneralUtility::_GET(Parameter::MODULE);
        return FormProtectionFactory::get()->generateToken('moduleCall', $moduleName);
    }

    /**
     * @param array $moduleCodes
     * @return string
     */
    protected function assembleTab(array $moduleCodes)
    {
        $tabs = [];
        foreach ($moduleCodes as $moduleCode => $title) {
            $dataType = $this->getDataTypeForModuleCode($moduleCode);
            $tabs[] = sprintf('<li %s><a href="%s">%s %s</a></li>',
                $this->getModuleLoader()->getVidiModuleCode() === $moduleCode ? 'class="active"' : '',
                $this->getModuleLoader()->getModuleUrl(array(Parameter::SUBMODULE => $moduleCode)),
                $this->getIconFactory()->getIconForRecord($dataType, [], Icon::SIZE_SMALL),
                $title
            );
        }
        return implode("\n", $tabs);
    }

    /**
     * @param $moduleCode
     * @return string
     */
    protected function getDataTypeForModuleCode($moduleCode)
    {
        return $GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode]['dataType'];
    }

    /**
     * @param array $moduleCodes
     * @return string
     */
    protected function assembleMenuOptions(array $moduleCodes)
    {
        $options = '';
        foreach ($moduleCodes as $moduleCode => $title) {
            $options .= sprintf('<option class="menu-dataType-item" value="%s" style="background-url(sysext/t3skin/icons/gfx/i/pages.gif)" %s>%s</option>%s',
                $moduleCode,
                $this->getModuleLoader()->getVidiModuleCode() === $moduleCode ? 'selected' : '',
                $title,
                chr(10)
            );
        }

        return $options;
    }

}
