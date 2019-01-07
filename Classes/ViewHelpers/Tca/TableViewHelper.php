<?php
namespace Fab\Vidi\ViewHelpers\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * View helper which wraps the TCA Table service.
 */
class TableViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', '', true);
        $this->registerArgument('dataType', 'string', '', false, '');
    }

    /**
     * Returns a value from the TCA Table service according to a key.
     *
     * @return string
     */
    public function render()
    {
        $key = $this->arguments['key'];
        $dataType = $this->arguments['dataType'];

        $result = Tca::table($dataType)->getTca();

        // Explode segment and loop around.
        $keys = explode('|', $key);
        foreach ($keys as $key) {
            if (!empty($result[$key])) {
                $result = $result[$key];
            } else {
                // not found value
                $result = false;
                break;
            }
        }

        return $result;
    }

}
