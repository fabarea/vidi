<?php
namespace Fab\Vidi\Persistence;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Query settings. This class is NOT part of the FLOW3 API.
 * It reflects the settings unique to TYPO3 4.x.
 *
 * @api
 */
class QuerySettings extends Typo3QuerySettings
{

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
    public function initializeObject()
    {
        parent::initializeObject();
    }
}
