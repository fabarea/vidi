<?php

namespace Fab\Vidi\View;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Abstract Component View.
 */
interface ViewComponentInterface
{
    /**
     * Renders something to be printed out to the browser.
     *
     * @return string
     */
    public function render();
}
