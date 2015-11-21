<?php
namespace Fab\Vidi\Backend;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Configuration\ConfigurationUtility;

/**
 * Display custom fields in the Extension Manager.
 */
class ExtensionManager
{

    /**
     * @var array
     */
    protected $excludedContentTypes = array('pages', 'pages_language_overlay', 'tx_rtehtmlarea_acronym');

    /**
     * Display a message to the Extension Manager whether the configuration is OK or KO.
     *
     * @param array $params
     * @param \TYPO3\CMS\Core\TypoScript\ConfigurationForm $tsObj
     * @return string
     */
    public function renderDataTypes(&$params, &$tsObj)
    {

        $configuration = ConfigurationUtility::getInstance()->getConfiguration();
        $selectedDataTypes = GeneralUtility::trimExplode(',', $configuration['data_types']);
        $options = '';
        foreach ($this->getDataTypes() as $dataType) {
            $checked = '';

            if (in_array($dataType, $selectedDataTypes)) {
                $checked = 'checked="checked"';
            }
            $options .= sprintf(
                '<li><label><input type="checkbox" class="fieldDataType" value="%s" %s /> %s</label></li>',
                $dataType,
                $checked,
                $dataType
            );
        }

        $menu = sprintf('<ul class="list-unstyled" style="margin-top: 10px;">%s</ul>', $options);

        // Assemble final output.
        $output = <<<EOF
            <div class="form-group form-group-dashed>
                <div class="form-control-wrap">

				    <div class="typo3-tstemplate-ceditor-row" id="userTS-dataTypes">
                    <script type="text/javascript">
                        (function($) {
                            $(function() {

                                // Handler which will concatenate selected data types.
                                $('.fieldDataType').change(function() {

                                    var dataTypes = $('#fieldDataTypes').val();
                                    var currentValue = $(this).val();

                                    // In any case remove item
                                    var expression = new RegExp(', *' + currentValue, 'i');
                                    dataTypes = dataTypes.replace(expression, '');
                                    $('#fieldDataTypes').val(dataTypes);

                                    // Append new data type at the end if checked.
                                    if ($(this).is(':checked')) {
                                        $('#fieldDataTypes').val(dataTypes + ', ' + currentValue);
                                    }
                                });
                            });
                        })(jQuery);
                    </script>
					<input type="text" class="form-control" id="fieldDataTypes" name="tx_extensionmanager_tools_extensionmanagerextensionmanager[config][data_types][value]" value="{$configuration['data_types']}" />
				</div>
                $menu
            </div>
EOF;

        return $output;
    }

    /**
     * @return array
     */
    public function getDataTypes()
    {

        $dataTypes = [];
        foreach ($GLOBALS['TCA'] as $contentType => $tca) {
            if (!in_array($contentType, $this->excludedContentTypes)
                && isset($GLOBALS['TCA'][$contentType]['ctrl']['label'])
                && (
                    !isset($GLOBALS['TCA'][$contentType]['ctrl']['hideTable'])
                    || TRUE !== (bool)$GLOBALS['TCA'][$contentType]['ctrl']['hideTable']
                )
            ) {
                $dataTypes[] = $contentType;
            }
        }
        return $dataTypes;
    }
}
