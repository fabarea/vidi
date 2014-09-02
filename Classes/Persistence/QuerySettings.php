<?php
namespace TYPO3\CMS\Vidi\Persistence;

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
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Query settings. This class is NOT part of the FLOW3 API.
 * It reflects the settings unique to TYPO3 4.x.
 *
 * @api
 */
class QuerySettings extends Typo3QuerySettings {

	/**
	 * Flag if the storage page should be respected for the query.
	 *
	 * @var boolean
	 */
	protected $respectStoragePage = FALSE;

	/**
	 * As long as we use a feature flag ignoreAllEnableFieldsInBe to determine the default behavior, the
	 * initializeObject is responsible for handling that.
	 */
	public function initializeObject() {
		parent::initializeObject();
	}
}
