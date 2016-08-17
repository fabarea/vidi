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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which returns Grid preferences.
 */
class PreferencesViewHelper extends AbstractViewHelper
{

    /**
     * Returns Grid preferences for the given key.
     *
     * @param string $key
     * @return mixed
     */
    public function render($key)
    {
        return $this->getModulePreferences()->get($key);
    }

    /**
     * @return ModulePreferences
     */
    protected function getModulePreferences()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Module\ModulePreferences');
    }

}
