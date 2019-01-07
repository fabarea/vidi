<?php
namespace Fab\Vidi\ViewHelpers;

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
 * View helper which connects the Module Loader object.
 */
class ModulePreferencesViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', 'The module key', true);
    }

    /**
     * Interface with the Module Loader
     *
     * @return string
     */
    public function render()
    {
        $getter = 'get' . ucfirst($this->arguments['key']);

        /** @var ModulePreferences $modulePreferences */
        $modulePreferences = GeneralUtility::makeInstance(ModulePreferences::class);
        return $modulePreferences->$getter();
    }

}
