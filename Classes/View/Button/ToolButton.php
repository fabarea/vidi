<?php

namespace Fab\Vidi\View\Button;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tool\ToolRegistry;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View helper which renders a dropdown menu for storage.
 */
class ToolButton extends AbstractComponentView
{
    /**
     * Renders a dropdown menu for storage.
     *
     * @return string
     */
    public function render()
    {
        $result = '';

        // Compute the additional parameters.
        $additionalParameters = array(
            $this->getModuleLoader()->getParameterPrefix() => array('controller' => 'Tool', 'action' => 'welcome'),
        );

        // Get current data type and tell whether there are registered tools.
        $dataType = $this->getModuleLoader()->getDataType();

        if (ToolRegistry::getInstance()->hasAnyTools($dataType)) {
            $result = sprintf(
                '<a href="%s&returnUrl=%s" class="btn btn-default btn-sm btn-doc-header" title="%s"><span class="t3-icon fa fa-cog" aria-hidden="true"></span></a>',
                $this->getModuleLoader()->getModuleUrl($additionalParameters),
                urlencode($this->getModuleLoader()->getModuleUrl()),
                $this->getLanguageService()->sL('LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:open_tools')
            );
        }
        return $result;
    }
}
