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
 * View helper which returns the label of a field.
 */
class LabelViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('dataType', 'string', '', true);
        $this->registerArgument('fieldName', 'string', '', true);
    }

    /**
     * Returns the label of a field
     *
     * @return string
     */
    public function render()
    {
        $dataType = $this->arguments['dataType'];
        $fieldName = $this->arguments['fieldName'];
        return Tca::table($dataType)->field($fieldName)->getLabel();
    }

}
