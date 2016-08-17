<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class for configuring the "Check Box" Grid Renderer.
 * @deprecated will be removed in Vidi 2.0 + 2. Use CheckBoxRenderer instead.
 */
class CheckBoxComponent extends ColumnRendererAbstract
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
     * Render a column in the Grid.
     *
     * @return string
     */
    public function render()
    {
        return 'Please, replace "CheckBoxComponent" by "CheckBoxRenderer" in TCA';
    }
}
