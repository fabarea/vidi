<?php
namespace Fab\Vidi\Tca;

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

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for field type.
 */
class FieldType extends Enumeration {

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