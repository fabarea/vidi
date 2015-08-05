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
 * An abstract class to handle TCA.
 */
abstract class AbstractTca implements TcaServiceInterface {

	/**
	 * Returns an instance of the current Backend User.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Returns whether the current mode is Backend.
	 *
	 * @return bool
	 */
	protected function isBackendMode() {
		return TYPO3_MODE == 'BE';
	}

	/**
	 * Returns whether the current mode is Frontend.
	 *
	 * @return bool
	 */
	protected function isFrontendMode() {
		return TYPO3_MODE == 'FE';
	}

}
