<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class for configuring a "Edit Relation" Grid Renderer in the Grid TCA.
 * @deprecated will be removed in Vidi 2.0 + 2. Use RelationEditRenderer instead.
 */
class RelationEditComponent extends ColumnRendererAbstract
{

    /**
     * Render a column in the Grid.
     *
     * @return string
     */
    public function render()
    {
        return 'Please, replace "RelationEditComponent" by "RelationEditRenderer" in TCA';
    }
}
