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
