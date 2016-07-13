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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for rendering the "Button Group" in the Grid, e.g. edit, delete, etc..
 */
class ButtonGroupRenderer extends ColumnRendererAbstract
{

    /**
     * Configure the "Button Group" Grid Renderer.
     */
    public function __construct()
    {
        $configuration = array(
            'sortable' => FALSE,
            'canBeHidden' => FALSE,
            'width' => '100px',
        );
        parent::__construct($configuration);
    }

    /**
     * Render the "Button Group" in the Grid, e.g. edit, delete, etc..
     *
     * @return string
     */
    public function render()
    {
        $components = $this->getModuleLoader()->getGridButtonsComponents();

        $buttons = [];
        foreach ($components as $component) {

            /** @var  $view */
            $view = GeneralUtility::makeInstance($component);
            $buttons[] = $view->render($this->getObject());
        }

        $output = sprintf(
            '<div class="btn-toolbar pull-right" role="toolbar" aria-label=""><div class="btn-group" role="group" aria-label="">%s</div></div>',
            implode("\n", $buttons)
        );

        return $output;
    }

}
