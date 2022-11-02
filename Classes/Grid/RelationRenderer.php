<?php

namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tool\AbstractTool;
use Fab\Vidi\Utility\BackendUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Imaging\Icon;

/**
 * Class rendering relation
 */
class RelationRenderer extends ColumnRendererAbstract
{
    /**
     * Render a representation of the relation on the GUI.
     *
     * @return string
     */
    public function render()
    {
        if (AbstractTool::isBackend()) {
            $output = $this->renderForBackend();
        } else {
            $output = $this->renderForFrontend();
        }

        return $output;
    }

    /**
     * @return string
     */
    protected function renderForBackend()
    {
        $output = '';

        // Get label of the foreign table.
        $foreignLabelField = $this->getForeignTableLabelField($this->fieldName);

        if (Tca::table($this->object)->field($this->fieldName)->hasOne()) {
            $foreignObject = $this->object[$this->fieldName];

            if ($foreignObject) {
                $output = sprintf(
                    '<a href="%s" data-uid="%s" class="btn-edit invisible">%s</a> <span>%s</span>',
                    $this->getEditUri($foreignObject),
                    $this->object->getUid(),
                    $this->getIconFactory()->getIcon('actions-document-open', Icon::SIZE_SMALL),
                    $foreignObject[$foreignLabelField]
                );
            }
        } elseif (Tca::table($this->object)->field($this->fieldName)->hasMany()) {
            if (!empty($this->object[$this->fieldName])) {
                /** @var $foreignObject \Fab\Vidi\Domain\Model\Content */
                foreach ($this->object[$this->fieldName] as $foreignObject) {
                    $output .= sprintf(
                        '<li><a href="%s" data-uid="%s" class="btn-edit invisible">%s</a> <span>%s</span></li>',
                        $this->getEditUri($foreignObject),
                        $this->object->getUid(),
                        $this->getIconFactory()->getIcon('actions-document-open', Icon::SIZE_SMALL),
                        $foreignObject[$foreignLabelField]
                    );
                }
                $output = sprintf('<ul class="list-unstyled">%s</ul>', $output);
            }
        }
        return $output;
    }

    /**
     * @return string
     */
    protected function renderForFrontend()
    {
        $output = '';

        // Get label of the foreign table.
        $foreignLabelField = $this->getForeignTableLabelField($this->fieldName);

        if (Tca::table($this->object)->field($this->fieldName)->hasOne()) {
            $foreignObject = $this->object[$this->fieldName];

            if ($foreignObject) {
                $output = sprintf(
                    '%s',
                    $foreignObject[$foreignLabelField]
                );
            }
        } elseif (Tca::table($this->object)->field($this->fieldName)->hasMany()) {
            if (!empty($this->object[$this->fieldName])) {
                /** @var $foreignObject \Fab\Vidi\Domain\Model\Content */
                foreach ($this->object[$this->fieldName] as $foreignObject) {
                    $output .= sprintf(
                        '<li>%s</li>',
                        $foreignObject[$foreignLabelField]
                    );
                }
                $output = sprintf('<ul class="list-unstyled">%s</ul>', $output);
            }
        }
        return $output;
    }

    /**
     * Render an edit URI given an object.
     *
     * @param Content $object
     * @return string
     */
    protected function getEditUri(Content $object)
    {
        $uri = BackendUtility::getModuleUrl(
            'record_edit',
            array(
                $this->getEditParameterName($object) => 'edit',
                'returnUrl' => $this->getModuleLoader()->getModuleUrl()
            )
        );
        return $uri;
    }

    /**
     * @param Content $object
     * @return string
     */
    protected function getEditParameterName(Content $object)
    {
        return sprintf(
            'edit[%s][%s]',
            $object->getDataType(),
            $object->getUid()
        );
    }

    /**
     * Return the label field of the foreign table.
     *
     * @param string $fieldName
     * @return string
     */
    protected function getForeignTableLabelField($fieldName)
    {
        // Get TCA table service.
        $table = Tca::table($this->object);

        // Compute the label of the foreign table.
        $relationDataType = $table->field($fieldName)->relationDataType();
        return Tca::table($relationDataType)->getLabelField();
    }
}
