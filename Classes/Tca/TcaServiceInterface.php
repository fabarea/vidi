<?php
namespace TYPO3\CMS\Vidi\Tca;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * An interface to handle TCA service
 */
interface TcaServiceInterface {

	const TYPE_TABLE = 'table';

	const TYPE_FIELD = 'field';

	const TYPE_GRID = 'grid';

	const TYPE_FORM = 'form';

	const TYPE_FACET = 'facet';
}
