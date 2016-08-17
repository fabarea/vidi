<?php
namespace Fab\Vidi\ViewHelpers\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * View helper which wraps the TCA Table service.
 */
class TableViewHelper extends AbstractViewHelper
{

    /**
     * Returns a value from the TCA Table service according to a key.
     *
     * @param string $key
     * @param string $dataType
     * @return string
     */
    public function render($key, $dataType = '')
    {
        $result = Tca::table($dataType)->getTca();

        // Explode segment and loop around.
        $keys = explode('|', $key);
        foreach ($keys as $key) {
            if (!empty($result[$key])) {
                $result = $result[$key];
            } else {
                // not found value
                $result = FALSE;
                break;
            }
        }

        return $result;
    }

}
