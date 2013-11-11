<?php
namespace TYPO3\CMS\Vidi;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class related to a possible plugin loaded inside a Vidi module.
 * It can be convenient to load additional stuff in a special context.
 * The plugin is requested by a GET parameter.
 * Example: tx_vidi_user_vidisysfilem1[plugins][]=imageEditor
 */
class ModulePlugin implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Vidi\ModuleLoader
	 * @inject
	 */
	protected $moduleLoader;

	/**
	 * Gets a singleton instance of this class.
	 *
	 * @return \TYPO3\CMS\Vidi\ModulePlugin
	 */
	static public function getInstance() {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		return $objectManager->get('TYPO3\CMS\Vidi\ModulePlugin');
	}

	/**
	 * @param string $pluginName
	 * @return bool
	 */
	public function isPluginRequired($pluginName) {
		$parameterPrefix = $this->moduleLoader->getParameterPrefix();
		$parameters = GeneralUtility::_GET($parameterPrefix);
		return !empty($parameters['plugins']) && is_array($parameters['plugins']) && in_array($pluginName, $parameters['plugins']);
	}

}


?>