<?php

namespace Fab\Vidi\Language;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for localization status.
 */
class LocalizationStatus extends Enumeration
{
    public const LOCALIZED = 'localized';
    public const NOT_YET_LOCALIZED = 'notYetLocalized';
    public const EMPTY_VALUE = 'emptyValue';
}
