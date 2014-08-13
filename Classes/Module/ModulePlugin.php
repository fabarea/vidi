<?php
namespace TYPO3\CMS\Vidi\Module;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class related to a possible plugin loaded inside a Vidi module.
 * It can be convenient to load additional stuff in a special context.
 * The plugin is requested by a GET parameter.
 * Example: tx_vidi_user_vidisysfilem1[plugins][]=imageEditor
 */
class ModulePlugin implements SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Vidi\Module\ModuleLoader
	 * @inject
	 */
	protected $moduleLoader;

	/**
	 * Gets a singleton instance of this class.
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModulePlugin
	 */
	static public function getInstance() {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		return $objectManager->get('TYPO3\CMS\Vidi\Module\ModulePlugin');
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
