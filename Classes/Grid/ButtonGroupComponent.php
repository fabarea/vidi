<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class for configuring a "Button Group" Grid Renderer.
 * @deprecated will be removed in Vidi 2.0 + 2. Use ButtonGroupRenderer instead.
 */
class ButtonGroupComponent extends ColumnRendererAbstract
{

    /**
     * Configure the "Button Group" Grid Renderer.
     */
    public function __construct()
    {
        $configuration = array(
            'sortable' => FALSE,
            'canBeHidden' => FALSE,
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
        return 'Please, replace "ButtonGroupComponent" by "ButtonGroupRenderer" in TCA';
    }
}
