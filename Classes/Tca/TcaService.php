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

/**
 * A class to handle TCA ctrl.
 * @deprecated use class "Tca" instead will be removed in vidi 0.7 + 2 version.
 */
class TcaService extends Tca {

	/**
	 * @deprecated -> use FieldType
	 */
	const TEXT = 'text';

	/**
	 * @deprecated -> use FieldType
	 */
	const NUMBER = 'number';

	/**
	 * @deprecated -> use FieldType
	 */
	const EMAIL = 'email';

	/**
	 * @deprecated -> use FieldType
	 */
	const DATE = 'date';

	/**
	 * @deprecated -> use FieldType
	 */
	const DATETIME = 'datetime';

	/**
	 * @deprecated -> use FieldType
	 */
	const TEXTAREA = 'textarea';

	/**
	 * @deprecated -> use FieldType
	 */
	const SELECT = 'select';

	/**
	 * @deprecated -> use FieldType
	 */
	const RADIO = 'radio';

	/**
	 * @deprecated -> use FieldType
	 */
	const CHECKBOX = 'check';

	/**
	 * @deprecated -> use FieldType
	 */
	const FILE = 'file';

	/**
	 * @deprecated -> use FieldType
	 */
	const MULTISELECT = 'multiselect';

	/**
	 * @deprecated -> use FieldType
	 */
	const TREE = 'tree';

}
