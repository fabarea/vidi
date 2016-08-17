<?php
namespace Fab\Vidi\Module;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for module name.
 */
class ModuleName extends Enumeration
{

    const WEB = 'web';

    const FILE = 'file';

    const USER = 'user';

    const ADMIN = 'admin';

    const SYSTEM = 'system';

}