<?php

namespace Fab\Vidi\ViewHelpers\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Module\ModulePreferences;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which returns Grid preferences.
 */
class PreferencesViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', '', true);
    }

    /**
     * Returns Grid preferences for the given key.
     *
     * @return mixed
     */
    public function render()
    {
        return $this->getModulePreferences()->get($this->arguments['key']);
    }

    /**
     * @return ModulePreferences|object
     */
    protected function getModulePreferences()
    {
        return GeneralUtility::makeInstance(ModulePreferences::class);
    }
}
