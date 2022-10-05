<?php

namespace Fab\Vidi\View\System;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View for rendering buttons in the grids according to a Content object.
 * @todo remove me in version 0.6 + 2 versions
 */
class ButtonsSystem extends AbstractComponentView
{
    /**
     * Rendering buttons in the grids given a Content object.
     *
     * @param Content $object
     * @return string
     */
    public function render(Content $object = null)
    {
        return '';
    }
}
