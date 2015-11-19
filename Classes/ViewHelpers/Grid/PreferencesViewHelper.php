<?php
namespace Fab\Vidi\ViewHelpers\Grid;

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
