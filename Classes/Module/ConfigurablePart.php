<?php

namespace Fab\Vidi\Module;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Enumeration object for preference name.
 */
class ConfigurablePart
{
    public const __default = '';
    public const EXCLUDED_FIELDS = 'excluded_fields';
    public const MENU_VISIBLE_ITEMS = 'menuVisibleItems';
    public const MENU_VISIBLE_ITEMS_DEFAULT = 'menuVisibleItemsDefault';

    /**
     * Get the valid values for this enum.
     *
     * @param boolean $include_default
     * @return array
     */
    public static function getParts($include_default = false)
    {
        return array(
            'EXCLUDED_FIELDS' => self::EXCLUDED_FIELDS,
            'MENU_VISIBLE_ITEMS' => self::MENU_VISIBLE_ITEMS,
            'MENU_VISIBLE_ITEMS_DEFAULT' => self::MENU_VISIBLE_ITEMS_DEFAULT,
        );
    }
}
