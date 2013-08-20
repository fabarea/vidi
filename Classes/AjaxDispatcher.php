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
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Service class used for dispatching Ajax request.
 * Code was inspired from this blog article:
 * http://daniel.lienert.cc/blog/blog-post/2011/04/23/typo3-extbase-und-ajax/
 */
class AjaxDispatcher {

	/**
	 * @var array
	 */
	static protected $allowedControllerActions = array();

	/**
	 * Array of all request Arguments
	 *
	 * @var array
	 */
	protected $requestArguments = array();

	/**
	 * Extbase Object Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var string
	 */
	protected $extensionName;

	/**
	 * @var string
	 */
	protected $pluginName;

	/**
	 * @var string
	 */
	protected $controllerName;

	/**
	 * @var string
	 */
	protected $actionName;

	/**
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * @var integer
	 */
	protected $pageUid;

	/**
	 * Initializes and dispatches actions
	 * Call this function if you want to use this dispatcher "standalone"
	 */
	public function initAndDispatch() {
		$this->initCallArguments()->dispatch();
	}

	/**
	 * Called by ajax.php / eID.php
	 * Builds an extbase context and returns the response
	 * ATTENTION: You should not call this method without initializing the dispatcher. Use initAndDispatch() instead!
	 */
	public function dispatch() {

//		/** @var \TYPO3\CMS\Extbase\Core\Bootstrap $bootstrap */
//		$bootstrap = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Core\Bootstrap');
//		$configuration = array(
//			'extensionName' => 'Vidi',
//			'pluginName' => 'VidiFeUsersM1',
//			'switchableControllerActions' => array(
//				'FrontendUser' => array('listFrontendUserGroup')
//			),
//		);
//		$content = $bootstrap->run('', $configuration);
		$configuration['extensionName'] = $this->extensionName;
		$configuration['pluginName'] = $this->pluginName;

		/** @var \TYPO3\CMS\Extbase\Core\Bootstrap $bootstrap */
		$bootstrap = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Core\Bootstrap');
		$bootstrap->initialize($configuration);

		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

		$request = $this->buildRequest();

		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
		$response = $this->objectManager->create('TYPO3\CMS\Extbase\Mvc\Web\Response');

		/** @var \TYPO3\CMS\Extbase\Mvc\Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Dispatcher');
		$dispatcher->dispatch($request, $response);

		$response->sendHeaders();
		$this->cleanShutDown();
		echo $response->getContent();
	}

	/**
	 * @return void
	 */
	public function cleanShutDown() {
		$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager')->persistAll();
		$this->objectManager->get('TYPO3\CMS\Extbase\Reflection\ReflectionService')->shutdown();
	}

	/**
	 * Build a request object
	 *
	 * @return \TYPO3\CMS\Extbase\Mvc\Web\Request $request
	 */
	protected function buildRequest() {
		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Request $request */
		$request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
		$request->setControllerExtensionName($this->extensionName);
		$request->setPluginName($this->pluginName);
		$request->setControllerName($this->controllerName);
		$request->setControllerActionName($this->actionName);
		$request->setArguments($this->arguments);

		return $request;
	}

	/**
	 * Prepare the call arguments
	 *
	 * @return $this
	 */
	public function initCallArguments() {
		$request = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('request');

		if ($request) {
			$this->setRequestArgumentsFromJSON($request);
		} else {
			$this->setRequestArgumentsFromGetPost();
		}

		$this->validateArguments();

		$this->extensionName = $this->requestArguments['extensionName'];
		$this->pluginName = $this->requestArguments['pluginName'];
		$this->controllerName = $this->requestArguments['controllerName'];
		$this->actionName = $this->requestArguments['actionName'];

		if (is_array($this->requestArguments['arguments'])) {
			$this->arguments = $this->requestArguments['arguments'];
		}

		return $this;
	}

	/**
	 * Set the request array from JSON
	 *
	 * @param string $request
	 */
	protected function setRequestArgumentsFromJSON($request) {
		$requestArray = json_decode($request, true);
		if (is_array($requestArray)) {
			$this->requestArguments = \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule($this->requestArguments, $requestArray);
		}
	}

	/**
	 * Set the request array from the getPost array
	 */
	protected function setRequestArgumentsFromGetPost() {
		$validArguments = array('extensionName', 'pluginName', 'controllerName', 'actionName', 'arguments');
		foreach ($validArguments as $argument) {
			if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP($argument)) $this->requestArguments[$argument] = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($argument);
		}
	}

	/**
	 * Check whether the arguments related to plugin / controller / action are allowed.
	 * Since an Extbase plugin will be instantiated based on these arguments,
	 * it is quite important to check whether they are allowed.
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function validateArguments() {
		$messageAppended = ' Forgotten to define \TYPO3\CMS\Vidi\AjaxDispatcher::addAllowedActions in ext_tables.php or wrong usage?';
		$extensionName = $this->requestArguments['extensionName'];
		if (empty(self::$allowedControllerActions[$extensionName])) {
			$message = sprintf('Extension name "%s" is not allowed.', $extensionName);
			throw new \Exception($message . $messageAppended, 1377018166);
		}

		$pluginName = $this->requestArguments['pluginName'];
		if (empty(self::$allowedControllerActions[$extensionName]['plugins'][$pluginName])) {
			$message = sprintf('Plugin name "%s" is not allowed.', $pluginName);
			throw new \Exception($message . $messageAppended, 1377018167);
		}

		$controllerName = $this->requestArguments['controllerName'];
		if (empty(self::$allowedControllerActions[$extensionName]['plugins'][$pluginName]['controllers'][$controllerName])) {
			$message = sprintf('Controller name "%s" is not allowed.', $controllerName);
			throw new \Exception($message . $messageAppended, 1377018168);
		}

		$actionName = $this->requestArguments['actionName'];
		if (!in_array($actionName, self::$allowedControllerActions[$extensionName]['plugins'][$pluginName]['controllers'][$controllerName]['actions'])) {
			$message = sprintf('Action name "%s" is not allowed.', $actionName);
			throw new \Exception($message . $messageAppended, 1377018169);
		}
	}

	/**
	 * Add allow actions.
	 *
	 * @param string $extensionName The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
	 * @param string $pluginName must be a unique id for your plugin in UpperCamelCase (the string length of the extension key added to the length of the plugin name should be less than 32!)
	 * @param array $controllerActions is an array of allowed combinations of controller and action stored in an array (controller name as key and a comma separated list of action names as value, the first controller and its first action is chosen as default)
	 * @return void
	 */
	static public function addAllowedActions($extensionName, $pluginName, array $controllerActions) {
		if (empty(self::$allowedControllerActions[$extensionName]['plugins'][$pluginName])) {
			self::$allowedControllerActions[$extensionName]['plugins'][$pluginName] = array();
		};

		foreach ($controllerActions as $controllerName => $actionsList) {
			self::$allowedControllerActions[$extensionName]['plugins'][$pluginName]['controllers'][$controllerName] = array('actions' => \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $actionsList));
		}
	}

	/**
	 * @param $extensionName
	 * @throws \Exception
	 * @return $this
	 */
	public function setExtensionName($extensionName) {
		if (!$extensionName) throw new \Exception('No extension name set for extbase request.', 1327583065);

		$this->extensionName = $extensionName;
		return $this;
	}

	/**
	 * @param $pluginName
	 * @return $this
	 */
	public function setPluginName($pluginName) {
		$this->pluginName = $pluginName;
		return $this;
	}

	/**
	 * @param $controllerName
	 * @return $this
	 */
	public function setControllerName($controllerName) {
		$this->controllerName = $controllerName;
		return $this;
	}

	/**
	 * @param $actionName
	 * @return $this
	 */
	public function setActionName($actionName) {
		$this->actionName = $actionName;
		return $this;
	}
}
