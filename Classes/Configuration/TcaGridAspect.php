<?php
namespace Fab\Vidi\Configuration;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Fab\Vidi\Grid\ButtonGroupRenderer;
use Fab\Vidi\Grid\CheckBoxRenderer;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('vidi');

        $dataTypes = GeneralUtility::trimExplode(',', $configuration['data_types'], true);

        if (ExtensionManagementUtility::isLoaded('vidi_frontend')) {

            $extendedConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('vidi_frontend');
            $vidiFrontendContentTypes = GeneralUtility::trimExplode(',', $extendedConfiguration['content_types'], true);
            $extendedDataTypes = array_merge($dataTypes, $vidiFrontendContentTypes);
            $dataTypes = array_unique($extendedDataTypes);
        }

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
            $GLOBALS['TCA'][$dataType]['grid'] = [];
        }

        if (empty($GLOBALS['TCA'][$dataType]['grid']['facets'])) {
            $GLOBALS['TCA'][$dataType]['grid']['facets'] = [
                'uid',
                $labelField,
            ];
        }

        if (empty($GLOBALS['TCA'][$dataType]['grid']['columns'])) {
            $GLOBALS['TCA'][$dataType]['grid']['columns'] = [
                '__checkbox' => [
                    'renderer' => CheckBoxRenderer::class,
                ],
                'uid' => [
                    'visible' => false,
                    'label' => '',
                    'width' => '5px',
                ],
                $labelField => [
                    'editable' => true,
                ],
                '__buttons' => [
                    'renderer' => ButtonGroupRenderer::class,
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
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

}
