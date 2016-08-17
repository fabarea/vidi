<?php
namespace Fab\Vidi\Processor;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Resolver\ContentObjectResolver;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Signal\ProcessContentDataSignalArguments;
use Fab\Vidi\Tca\Tca;

/**
 * Marker Utility class for replacing "known" markers within an expression.
 */
class MarkerProcessor implements SingletonInterface
{

    /**
     * @var array
     */
    protected $wellKnownMarkers = array(
        '{*}',
        '{counter}',
        '{date}',
        '{creation_date}'
    );

    /**
     * @param ProcessContentDataSignalArguments $signalArguments
     * @return array
     */
    public function processMarkers(ProcessContentDataSignalArguments $signalArguments)
    {

        $contentData = $signalArguments->getContentData();
        $creationTime = $this->getCreationTime($signalArguments);

        // Process markers
        foreach ($signalArguments->getContentData() as $fieldName => $updateValue) {
            if (is_scalar($updateValue)) {

                $currentValue = $this->getContentObjectResolver()->getValue(
                    $signalArguments->getContentObject(),
                    $signalArguments->getFieldNameAndPath(),
                    $fieldName,
                    $signalArguments->getLanguage()
                );
                $counter = $signalArguments->getCounter();

                $updateValue = $this->searchAndReplace($updateValue, $currentValue);
                $updateValue = $this->replaceWellKnownMarkers($updateValue, $currentValue, $counter, $creationTime);

                $contentData[$fieldName] = $updateValue;
            }
        }

        $signalArguments->setContentData($contentData);
        return array($signalArguments);
    }

    /**
     * @param string $updateValue
     * @param string $currentValue
     * @param int $counter
     * @param $creationTime
     * @return string
     */
    protected function replaceWellKnownMarkers($updateValue, $currentValue, $counter, $creationTime)
    {

        // Replaces values.
        $replaces = array(
            $currentValue,
            $counter,
            date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']),
            date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $creationTime),
        );

        // Replace me!
        return str_replace($this->wellKnownMarkers, $replaces, $updateValue);
    }

    /**
     * @param string $updateValue
     * @param string $currentValue
     * @return string
     */
    protected function searchAndReplace($updateValue, $currentValue)
    {

        if (strpos($updateValue, 's/') !== FALSE) {
            $structure = explode('/', $updateValue);
            $search = $structure[1];
            $replace = $structure[2];

            // Perhaps needs to be improved here if $search contains "/" precisely.
            $updateValue = preg_replace('/' . $search . '/isU', $replace, $currentValue);
        }
        return $updateValue;
    }

    /**
     * @param ProcessContentDataSignalArguments $signalArguments
     * @return int
     */
    protected function getCreationTime(ProcessContentDataSignalArguments $signalArguments)
    {
        $creationTime = 0;
        $creationTimeField = Tca::table($signalArguments->getContentObject()->getDataType())->getTimeCreationField();
        if ($creationTimeField) {
            $creationTime = $this->getContentObjectResolver()->getValue(
                $signalArguments->getContentObject(),
                $signalArguments->getFieldNameAndPath(),
                $creationTimeField
            );
        }
        return $creationTime;
    }

    /**
     * @return ContentObjectResolver
     */
    protected function getContentObjectResolver()
    {
        return GeneralUtility::makeInstance(ContentObjectResolver::class);
    }

}
