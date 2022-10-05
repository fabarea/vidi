<?php

namespace Fab\Vidi\ViewHelpers\Grid\Column;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Resolver\FieldPathResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * Tells whether the current field name has a relation to the main content (given by the Module Loader implicitly).
 */
class HasRelationViewHelper extends AbstractViewHelper
{
    /**
     * Return whether the current field name has a relation to the main content.
     *
     * @return boolean
     */
    public function render()
    {
        $fieldNameAndPath = $this->templateVariableContainer->get('columnName');
        $dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
        $fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);
        $hasRelation = Tca::table($dataType)->field($fieldName)->hasRelation();
        return $hasRelation;
    }

    /**
     * @return FieldPathResolver|object
     */
    protected function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance(FieldPathResolver::class);
    }
}
