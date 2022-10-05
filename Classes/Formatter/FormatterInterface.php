<?php

namespace Fab\Vidi\Formatter;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Format a value to be displayed in a Grid
 */
interface FormatterInterface
{
    /**
     * Format a date
     *
     * @param string $value
     * @return string
     */
    public function format($value);
}
