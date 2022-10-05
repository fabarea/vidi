<?php

namespace Fab\Vidi\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * An interface to handle TCA service
 */
interface TcaServiceInterface
{
    public const TYPE_TABLE = 'table';

    public const TYPE_FIELD = 'field';

    public const TYPE_GRID = 'grid';

    public const TYPE_FORM = 'form';

    public const TYPE_FACET = 'facet';
}
