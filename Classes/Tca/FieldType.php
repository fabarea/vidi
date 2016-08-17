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

    const TEXT = 'text';

    const NUMBER = 'number';

    const EMAIL = 'email';

    const DATE = 'date';

    const DATETIME = 'datetime';

    const TEXTAREA = 'textarea';

    const SELECT = 'select';

    const RADIO = 'radio';

    const CHECKBOX = 'check';

    const FILE = 'file';

    const MULTISELECT = 'multiselect';

    const TREE = 'tree';

}