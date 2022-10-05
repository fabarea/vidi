<?php

namespace Fab\Vidi\Language;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use Fab\Vidi\Service\DataService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Tca\Tca;

/**
 * A class for handling language in the Backend.
 */
class LanguageService implements SingletonInterface
{
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
    public function getLanguages()
    {
        if ($this->languages === null) {
            $this->languages = $this->getDataService()->getRecords('sys_language');
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
    public function getLocalizedContent(Content $object, $language)
    {
        // We want to cache data per Content object. Retrieve the Object hash.
        $objectHash = spl_object_hash($object);

        // Initialize the storage
        if (empty($this->localizedRecordStorage[$objectHash])) {
            $this->localizedRecordStorage[$objectHash] = [];
        }

        if (empty($this->localizedRecordStorage[$objectHash][$language])) {
            $localizedRecord = $this->getDataService()->getRecord(
                $object->getDataType(),
                [
                    Tca::table($object)->getLanguageParentField() => $object->getUid(), // e.g. l10n_parent
                    Tca::table($object)->getLanguageField() => $language, // e.g. sys_language_uid
                ]
            );

            if ($localizedRecord) {
                $localizedContent = GeneralUtility::makeInstance(Content::class, $object->getDataType(), $localizedRecord);
                $this->localizedRecordStorage[$objectHash][$language] = $localizedContent;
            } else {
                $this->localizedRecordStorage[$objectHash][$language] = []; // We want an array at least, even if empty.
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
    public function hasLocalization(Content $object, $language)
    {
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
    public function getLocalizedFieldName(Content $object, $language, $fieldName)
    {
        $localizedRecord = $this->getLocalizedContent($object, $language);
        return empty($localizedRecord) ? '' : $localizedRecord[$fieldName];
    }

    /**
     * Returns the default language configured by TSConfig.
     *
     * @return array
     */
    public function getDefaultFlag()
    {
        if ($this->defaultIcon === null) {
            $defaultFlag = ''; // default value

            $tsConfig = BackendUtility::getPagesTSconfig(0, 'mod.SHARED');

            // Fallback non sprite-configuration
            if (($pos = strrpos($tsConfig['properties']['defaultLanguageFlag'], '.')) !== false) {
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
    public function hasLanguages()
    {
        $languages = $this->getLanguages();
        return !empty($languages);
    }

    /**
     * Tell whether the given language exists.
     *
     * @param int $language
     * @return bool
     */
    public function languageExists($language)
    {
        $languages = $this->getLanguages();

        $LanguageExists = false;
        foreach ($languages as $_language) {
            if ((int)$_language['uid'] === (int)$language) {
                $LanguageExists = true;
                break;
            }
        }

        return $LanguageExists;
    }

    /**
     * @return object|DataService
     */
    protected function getDataService(): DataService
    {
        return GeneralUtility::makeInstance(DataService::class);
    }
}
