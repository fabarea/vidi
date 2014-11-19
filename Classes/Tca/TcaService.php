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
 * A class to handle TCA ctrl.
 * @deprecated use class "Tca" instead will be removed in vidi 0.7 + 2 version.
 */
class TcaService extends Tca {

	// @todo move me to an enumeration
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
