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
 * This class was used to set "respectStoragePage" before TYPO3 CMS 8 migration.
 * Feature was simply removed and permantely set to "false".
 * Class could be removed at one point
 *
 * Query settings. This class is NOT part of the FLOW3 API.
 * It reflects the settings unique to TYPO3 4.x.
 *
 * @api
 */
class QuerySettings extends Typo3QuerySettings
{

}
