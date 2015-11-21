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
