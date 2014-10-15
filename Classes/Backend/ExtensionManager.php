<?php
namespace TYPO3\CMS\Vidi\Backend;

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
use TYPO3\CMS\Vidi\Configuration\ConfigurationUtility;

/**
 * Display custom fields in the Extension Manager.
 */
class ExtensionManager {

	/**
	 * @var string
	 */
	protected $extKey = 'vidi';

	/**
	 * @var array
	 */
	protected $dataTypes = array('fe_users', 'fe_groups');

	/**
	 * Display a message to the Extension Manager whether the configuration is OK or KO.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\TypoScript\ConfigurationForm $tsObj
	 * @return string
	 */
	public function renderDataTypes(&$params, &$tsObj) {

		$configuration = ConfigurationUtility::getInstance()->getConfiguration();
		$dataTypes = GeneralUtility::trimExplode(',', $configuration['data_types']);

		$options = '';
		foreach ($this->dataTypes as $dataType) {
			$checked = '';

			if (in_array($dataType, $dataTypes)) {
				$checked = 'checked="checked"';
			}
			$options .= '<label><input type="checkbox" class="fieldDataType" value="' . $dataType . '" ' . $checked . ' /> ' . $dataType . '</label>';
		}

		$output = <<<EOF
				<div class="typo3-tstemplate-ceditor-row" id="userTS-dataTypes">
					<script type="text/javascript">
						(function($) {
						    $(function() {

								// Handler which will concatenate selected data types.
								$('.fieldDataType').change(function() {
									var selected = [];

									$('.fieldDataType').each(function(){
										if ($(this).is(':checked')) {
											selected.push($(this).val());
										}
									});

									$('#fieldDataTypes').val(selected.join(','));
								});
						    });
						})(jQuery);
					</script>
					$options

					<input type="hidden" id="fieldDataTypes" name="tx_extensionmanager_tools_extensionmanagerextensionmanager[config][data_types][value]" value="{$configuration['data_types']}" />
				</div>
EOF;

		return $output;
	}
}
