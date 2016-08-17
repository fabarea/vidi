<?php
namespace Fab\Vidi\DataHandler;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for process action.
 */
class ProcessAction extends Enumeration
{

    const REMOVE = 'remove';

    const UPDATE = 'update';

    const COPY = 'copy';

    const MOVE = 'move';

    const LOCALIZE = 'localize';

}
