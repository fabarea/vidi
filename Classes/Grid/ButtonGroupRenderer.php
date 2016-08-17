<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
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
