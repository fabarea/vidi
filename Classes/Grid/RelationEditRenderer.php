<?php
namespace Fab\Vidi\Grid;

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

use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Imaging\Icon;

/**
 * Class for editing mm relation between objects.
 */
class RelationEditRenderer extends ColumnRendererAbstract
{

    /**
     * Render a representation of the relation on the GUI.
     *
     * @return string
     */
    public function render()
    {

        $template = '<div style="text-align: right" class="pull-right invisible"><a href="%s" class="btn-edit-relation" data-field-label="%s">%s</a></div>';

        // Initialize url parameters array.
        $urlParameters = array(
            $this->getModuleLoader()->getParameterPrefix() => array(
                'controller' => 'Content',
                'action' => 'edit',
                'matches' => array('uid' => $this->object->getUid()),
                'fieldNameAndPath' => $this->getFieldName(),
            ),
        );

        $fieldLabel = Tca::table()->field($this->getFieldName())->getLabel();
        if ($fieldLabel) {
            $fieldLabel = str_replace(':', '', $fieldLabel); // sanitize label
        }

        $result = sprintf(
            $template,
            $this->getModuleLoader()->getModuleUrl($urlParameters),
            $fieldLabel,
            $this->getIconFactory()->getIcon('actions-edit-add', Icon::SIZE_SMALL)
        );

        return $result;
    }
}
