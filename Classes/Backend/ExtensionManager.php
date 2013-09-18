<?php
namespace TYPO3\CMS\Vidi\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ExtensionManager {

	/**
	 * @var string
	 */
	protected $extKey = 'vidi';

	/**
	 * @var string
	 */
	protected $dataTypes = array('fe_users', 'fe_groups');

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseHandler;

	/**
	 * Constructor
	 *
	 * @return \TYPO3\CMS\Vidi\Backend\ExtensionManager
	 */
	public function __construct() {

		/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

		/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
		$configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
		$configuration = $configurationUtility->getCurrentConfiguration($this->extKey);

		foreach ($configuration as $key => $data) {
			$this->configuration[$key] = $data['value'];
		}

		// Merge with Data that comes from the User
		$postData = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST();
		if (!empty($postData['data'])) {
			$this->settings = array_merge($this->settings, $postData['data']);
		}

		$this->databaseHandler = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Display a message to the Extension Manager whether the configuration is OK or KO.
	 *
	 * @param array $params
	 * @param object $tsObj t3lib_tsStyleConfig
	 * @return string the HTML message
	 */
	public function renderDataTypes(&$params, &$tsObj) {
		$dataTypes = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->configuration['data_types']);

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
						$(document).ready(function() {

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
					</script>
					$options
					<input type="hidden" id="fieldDataTypes" name="tx_extensionmanager_tools_extensionmanagerextensionmanager[config][data_types][value]" value="" />
				</div>
EOF;

		return $output;
	}
}

?>