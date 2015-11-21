<?php
namespace Fab\Vidi\Configuration;

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

use Fab\Vidi\Grid\ButtonGroupRenderer;
use Fab\Vidi\Grid\CheckBoxRenderer;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

/**
 * Add a Grid TCA to each "data type" enabling to display a Vidi module in the BE.
 */
class TcaGridAspect implements TableConfigurationPostProcessingHookInterface
{

    /**
     * Scans each data type of the TCA and add a Grid TCA if missing.
     *
     * @return array
     */
    public function processData()
    {

        /** @var ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->getObjectManager()->get(ConfigurationUtility::class);
        $configuration = $configurationUtility->getCurrentConfiguration('vidi');

        $dataTypes = GeneralUtility::trimExplode(',', $configuration['data_types']['value'], TRUE);

        foreach ($dataTypes as $dataType) {
            $this->ensureMinimumTcaForGrid($dataType);
        }

        return array($GLOBALS['TCA']);
    }

    /**
     * @param string $dataType
     */
    protected function ensureMinimumTcaForGrid($dataType)
    {
        $labelField = $this->getLabelField($dataType);
        if (empty($GLOBALS['TCA'][$dataType]['grid'])) {
            $GLOBALS['TCA'][$dataType]['grid'] = array();
        }

        if (empty($GLOBALS['TCA'][$dataType]['grid']['facet'])) {
            $GLOBALS['TCA'][$dataType]['grid']['facets'] = [
                'uid',
                $labelField,
            ];
        }

        if (empty($GLOBALS['TCA'][$dataType]['grid']['columns'])) {
            $GLOBALS['TCA'][$dataType]['grid']['columns'] = [
                '__checkbox' => [
                    'renderer' => new CheckBoxRenderer(),
                ],
                'uid' => [
                    'visible' => FALSE,
                    'label' => 'Id',
                    'width' => '5px',
                ],
                $labelField => [
                    'editable' => TRUE,
                ],
                '__buttons' => [
                    'renderer' => new ButtonGroupRenderer(),
                ],
            ];
        }
    }

    /**
     * Get the label name of table name.
     *
     * @param string $dataType
     * @return bool
     */
    protected function getLabelField($dataType)
    {
        return $GLOBALS['TCA'][$dataType]['ctrl']['label'];
    }

    /**
     * Tell whether the table has a label field.
     *
     * @param string $dataType
     * @return bool
     */
    protected function hasLabelField($dataType)
    {
        return isset($GLOBALS['TCA'][$dataType]['ctrl']['label']);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
    }

}