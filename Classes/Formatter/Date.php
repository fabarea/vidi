<?php
namespace Fab\Vidi\Formatter;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Format a date that will be displayed in the Grid
 */
class Date implements FormatterInterface, SingletonInterface
{

    /**
     * Format a date
     *
     * @param int $value
     * @return string
     * @throws \Exception
     */
    public function format($value)
    {
        $result = '';
        if ((int)$value > 0) {

            $timeStamp = '@' . $value;
            try {
                $date = new \DateTime($timeStamp);
                $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } catch (\Exception $exception) {
                throw new \Exception('"' . $timeStamp . '" could not be parsed by \DateTime constructor: ' . $exception->getMessage(), 1447153621);
            }

            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'Y-m-d';
            if (strpos($format, '%') !== false) {
                $result = strftime($format, $date->format('U'));
            } else {
                $result = $date->format($format);
            }
        }
        return $result;
    }

}
