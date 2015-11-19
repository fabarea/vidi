<?php
namespace Fab\Vidi\ViewHelpers;

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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which tells whether an argument exists.
 */
class GpViewHelper extends AbstractViewHelper
{

    /**
     * Tells whether the argument exists or not.
     *
     * @param string $argument
     * @param bool $encode
     * @return boolean
     */
    public function render($argument, $encode = TRUE)
    {
        $value = ''; // default value

        // Merge parameters
        $parameters = GeneralUtility::_GET();
        $post = GeneralUtility::_POST();
        ArrayUtility::mergeRecursiveWithOverrule($parameters, $post);

        // Traverse argument parts and retrieve value.
        $argumentParts = GeneralUtility::trimExplode('|', $argument);
        foreach ($argumentParts as $argumentPart) {
            if (isset($parameters[$argumentPart])) {
                $value = $parameters[$argumentPart];
                $parameters = $value;
            }
        }

        // Possible url encode.
        if ($encode) {
            $value = urlencode($value);
        }
        return $value;
    }
}
