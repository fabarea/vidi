<?php
namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Return the public path to Vidi extension.
 */
class PublicPathViewHelper extends AbstractViewHelper
{

    /**
     * Returns the public path to Vidi extension.
     *
     * @return string
     */
    public function render()
    {
        return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('vidi');
    }
}
