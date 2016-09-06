<?php
namespace Fab\Vidi\ViewHelpers\Grid\Column;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Exception\NotExistingFieldException;
use Fab\Vidi\Tca\Tca;

/**
 * View helper for rendering configuration that will be consumed by Javascript
 */
class ConfigurationViewHelper extends AbstractViewHelper
{

    /**
     * Render the columns of the grid.
     *
     * @throws NotExistingFieldException
     * @return string
     */
    public function render()
    {
        $output = '';

        foreach (Tca::grid()->getFields() as $fieldNameAndPath => $configuration) {

            // Early failure if field does not exist.
            if (!$this->isAllowed($fieldNameAndPath)) {
                $message = sprintf('Property "%s" does not exist!', $fieldNameAndPath);
                throw new NotExistingFieldException($message, 1375369594);
            }

            // mData vs columnName
            // -------------------
            // mData: internal name of DataTable plugin and can not contains a path, e.g. metadata.title
            // columnName: whole field name with path
            $output .= sprintf('Vidi._columns.push({ "data": "%s", "sortable": %s, "visible": %s, "width": "%s", "class": "%s", "columnName": "%s" });' . PHP_EOL,
                $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath), // Suitable field name for the DataTable plugin.
                Tca::grid()->isSortable($fieldNameAndPath) ? 'true' : 'false',
                Tca::grid()->isVisible($fieldNameAndPath) ? 'true' : 'false',
                Tca::grid()->getWidth($fieldNameAndPath),
                Tca::grid()->getClass($fieldNameAndPath),
                $fieldNameAndPath
            );
        }

        return $output;
    }

    /**
     * Tell whether the field looks ok to be displayed within the Grid.
     *
     * @param string $fieldNameAndPath
     * @return boolean
     */
    protected function isAllowed($fieldNameAndPath)
    {
        $dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
        $fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

        $isAllowed = false;
        if (Tca::grid()->hasRenderers($fieldNameAndPath)) {
            $isAllowed = true;
        } elseif (Tca::table()->field($fieldNameAndPath)->isSystem() || Tca::table($dataType)->hasField($fieldName)) {
            $isAllowed = true;
        }

        return $isAllowed;
    }

    /**
     * @return \Fab\Vidi\Resolver\FieldPathResolver
     */
    protected function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Resolver\FieldPathResolver');
    }
}
