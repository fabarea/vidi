<?php

namespace Fab\Vidi\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for field type.
 */
class FieldType extends Enumeration
{
    public const TEXT = 'text';

    public const NUMBER = 'number';

    public const EMAIL = 'email';

    public const DATE = 'date';

    public const DATETIME = 'datetime';

    public const TEXTAREA = 'textarea';

    public const SELECT = 'select';

    public const RADIO = 'radio';

    public const CHECKBOX = 'check';

    public const FILE = 'file';

    public const MULTISELECT = 'multiselect';

    public const TREE = 'tree';
}
