<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 *  Interface for configuring a column in the Grid.
 * @deprecated will be removed in Vidi 2.0 + 2. Use ButtonGroupRenderer instead.
 */
interface ColumnInterface
{
    /**
     * @return array
     */
    public function getConfiguration();

}
