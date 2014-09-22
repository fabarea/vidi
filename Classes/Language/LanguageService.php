<?php
namespace TYPO3\CMS\Vidi\Language;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * A class for handling language in the Backend.
 */
class LanguageService implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $languages;

	/**
	 * @var array
	 */
	protected $defaultIcon;

	/**
	 * Store the localized records to boost up performance.
	 *
	 * @var array
	 */
	protected $localizedRecordStorage;

	/**
	 * Returns available language records.
	 * The method stores the records in the property to speed up the process as the method can be often called.
	 *
	 * @return array
	 */
	public function getLanguages() {
		if (is_null($this->languages)) {

			$tableName = 'sys_language';

			$clause = '1 = 1';
			$clause .= BackendUtility::deleteClause($tableName);
			$clause .= BackendUtility::BEenableFields($tableName);
			$this->languages = $this->getDatabaseConnection()->exec_SELECTgetRows('*', $tableName, $clause);
		}
		return $this->languages;
	}

	/**
	 * Returns a localized record according to a Content object and a language identifier.
	 * Notice! This method does not overlay anything but simply returns the raw localized record.
	 *
	 * @param Content $object
	 * @param int $language
	 * @return Content
	 */
	public function getLocalizedContent(Content $object, $language) {

		// We want to cache data per Content object. Retrieve the Object hash.
		$objectHash = spl_object_hash($object);

		// Initialize the storage
		if (empty($this->localizedRecordStorage[$objectHash])) {
			$this->localizedRecordStorage[$objectHash] = array();
		}

		if (empty($this->localizedRecordStorage[$objectHash][$language])) {

			$clause = sprintf('%s = %s AND %s = %s',
				TcaService::table($object)->getLanguageParentField(), // e.g. l10n_parent
				$object->getUid(),
				TcaService::table($object)->getLanguageField(), // e.g. sys_language_uid
				$language
			);

			$clause .= BackendUtility::deleteClause($object->getDataType());
			$localizedRecord = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('*', $object->getDataType(), $clause);

			if ($localizedRecord) {
				$localizedContent = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Domain\Model\Content', $object->getDataType(), $localizedRecord);
				$this->localizedRecordStorage[$objectHash][$language] = $localizedContent;
			} else {
				$this->localizedRecordStorage[$objectHash][$language] = array(); // We want an array at least, even if empty.
			}
		}

		return $this->localizedRecordStorage[$objectHash][$language];
	}

	/**
	 * Tell whether the given Content object has a localization.
	 *
	 * @param Content $object
	 * @param int $language
	 * @return string
	 */
	public function hasLocalization(Content $object, $language) {
		$localizedRecord = $this->getLocalizedContent($object, $language);
		return !empty($localizedRecord);
	}

	/**
	 * Returns a localized field according to a Content object and a language identifier.
	 * Notice! If there is not translation, simply returns an empty string.
	 *
	 * @param Content $object
	 * @param int $language
	 * @param string $fieldName
	 * @return string
	 */
	public function getLocalizedFieldName(Content $object, $language, $fieldName) {
		$localizedRecord = $this->getLocalizedContent($object, $language);
		return empty($localizedRecord) ? '' : $localizedRecord[$fieldName];
	}

	/**
	 * Returns the default language configured by TSConfig.
	 *
	 * @return array
	 */
	public function getDefaultFlag() {

		if (is_null($this->defaultIcon)) {

			$defaultFlag = ''; // default value

			$tsConfig = BackendUtility::getModTSconfig(0, 'mod.SHARED');

			// Fallback non sprite-configuration
			if (($pos = strrpos($tsConfig['properties']['defaultLanguageFlag'], '.')) !== FALSE) {
				$defaultFlag = substr($tsConfig['properties']['defaultLanguageFlag'], 0, $pos);
			}

			$this->defaultIcon = $defaultFlag;
		}

		return $this->defaultIcon;
	}

	/**
	 * Returns whether the system includes language records.
	 *
	 * @return bool
	 */
	public function hasLanguages() {
		$languages = $this->getLanguages();
		return !empty($languages);
	}

	/**
	 * Tell whether the given language exists.
	 *
	 * @param int $language
	 * @return bool
	 */
	public function languageExists($language) {
		$languages = $this->getLanguages();

		$LanguageExists = FALSE;
		foreach ($languages as $_language) {
			if ((int)$_language['uid'] === (int)$language) {
				$LanguageExists = TRUE;
				break;
			}
		}

		return $LanguageExists;
	}

	/**
	 * Returns a pointer to the database.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
