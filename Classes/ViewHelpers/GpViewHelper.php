<?php
namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which tells whether an argument exists.
 */
class GpViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('argument', 'string', 'The argument name', true);
        $this->registerArgument('encode', 'bool', 'Whether to encode the URL.', false, true);
    }

    /**
     * Tells whether the argument exists or not.
     *
     * @return boolean
     */
    public function render()
    {
        $value = ''; // default value

        // Merge parameters
        $parameters = GeneralUtility::_GET();
        $post = GeneralUtility::_POST();
        ArrayUtility::mergeRecursiveWithOverrule($parameters, $post);

        // Traverse argument parts and retrieve value.
        $argumentParts = GeneralUtility::trimExplode('|', $this->arguments['argument']);
        foreach ($argumentParts as $argumentPart) {
            if (isset($parameters[$argumentPart])) {
                $value = $parameters[$argumentPart];
                $parameters = $value;
            }
        }

        // Possible url encode.
        if ($this->arguments['encode']) {
            $value = urlencode($value);
        }
        return $value;
    }
}
