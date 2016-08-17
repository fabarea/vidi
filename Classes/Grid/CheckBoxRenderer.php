<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class for rendering the "Check Box" in the Grid.
 */
class CheckBoxRenderer extends ColumnRendererAbstract
{

    /**
     * Configure the "Check Box" Grid Renderer.
     */
    public function __construct()
    {
        $configuration = array(
            'width' => '5px',
            'sortable' => FALSE,
            'canBeHidden' => FALSE,
            'html' => '<input type="checkbox" class="checkbox-row-top"/>',
        );
        parent::__construct($configuration);
    }

    /**
     * Render the "Check Box" in the Grid.
     *
     * @return string
     */
    public function render()
    {
        return sprintf('<input type="checkbox" class="checkbox-row" data-index="%s" data-uid="%s"/>',
            $this->getRowIndex(),
            $this->getObject()->getUid()
        );
    }
}
