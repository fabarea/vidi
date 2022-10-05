<?php

namespace Fab\Vidi\ViewHelpers\Grid\Column;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Computes the final field name in the context of the Grid.
 */
class FieldNameViewHelper extends AbstractViewHelper
{
    /**
     * Return the final field name in the context of the Grid.
     *
     * @return string
     */
    public function render()
    {
        $fieldName = $this->templateVariableContainer->get('columnName');
        $configuration = $this->templateVariableContainer->get('configuration');

        if (isset($configuration['dataType'])) {
            $fieldName = $configuration['dataType'] . '.' . $fieldName;
        }

        return $fieldName;
    }
}
