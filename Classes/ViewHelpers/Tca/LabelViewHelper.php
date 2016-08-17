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
 * View helper which returns the label of a field.
 */
class LabelViewHelper extends AbstractViewHelper
{

    /**
     * Returns the label of a field
     *
     * @param string $dataType
     * @param string $fieldName
     * @return string
     */
    public function render($dataType, $fieldName)
    {
        return Tca::table($dataType)->field($fieldName)->getLabel();
    }

}
