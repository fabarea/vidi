<?php
namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which connects the Module Loader object.
 */
class ModulePreferencesViewHelper extends AbstractViewHelper
{

    /**
     * Interface with the Module Loader
     *
     * @param string $key
     * @return string
     */
    public function render($key)
    {
        $getter = 'get' . ucfirst($key);

        /** @var \Fab\Vidi\Module\ModulePreferences $modulePreferences */
        $modulePreferences = $this->objectManager->get('Fab\Vidi\Module\ModulePreferences');
        return $modulePreferences->$getter();
    }

}
