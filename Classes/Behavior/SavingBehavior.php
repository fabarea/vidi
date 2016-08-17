<?php
namespace Fab\Vidi\Behavior;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for saving behavior.
 */
class SavingBehavior extends Enumeration
{

    const REMOVE = 'remove';

    const APPEND = 'append';

    const REPLACE = 'replace';

}