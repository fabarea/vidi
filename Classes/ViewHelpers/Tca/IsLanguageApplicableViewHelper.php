<?php
namespace Fab\Vidi\ViewHelpers\Tca;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
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
