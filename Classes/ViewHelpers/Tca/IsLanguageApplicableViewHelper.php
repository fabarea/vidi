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
 * View helper which tells whether the field in the context is localizable or not.
 */
class IsLanguageApplicableViewHelper extends AbstractViewHelper
{

    /**
     * Returns whether the field in the context is localizable or not.
     *
     * @return string
     */
    public function render()
    {
        $dataType = $this->templateVariableContainer->get('dataType');
        $fieldName = $this->templateVariableContainer->get('fieldName');

        $isLanguageApplicable = Tca::table($dataType)->hasLanguageSupport() && Tca::table($dataType)->field($fieldName)->isLocalized();
        return $isLanguageApplicable;
    }

}
