<?php
namespace Fab\Vidi\Tool;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Relation Analyser for a Vidi module.
 */
class RelationAnalyserTool extends AbstractTool {

	/**
	 * Display the title of the tool on the welcome screen.
	 *
	 * @return string
	 */
	public function getTitle() {
		return LocalizationUtility::translate(
			'analyse_relations',
			'vidi'
		);
	}

	/**
	 * Display the description of the tool in the welcome screen.
	 *
	 * @return string
	 */
	public function getDescription() {
		$templateNameAndPath = 'EXT:vidi/Resources/Private/Backend/Standalone/Tool/RelationAnalyser/Launcher.html';
		$view = $this->initializeStandaloneView($templateNameAndPath);
		$view->assign('sitePath', PATH_site);
		$view->assign('dataType', $this->getModuleLoader()->getDataType());
		return $view->render();
	}

	/**
	 * Do the job
	 *
	 * @param array $arguments
	 * @return string
	 */
	public function work(array $arguments = array()) {

		$templateNameAndPath = 'EXT:vidi/Resources/Private/Backend/Standalone/Tool/RelationAnalyser/WorkResult.html';
		$view = $this->initializeStandaloneView($templateNameAndPath);

		$dataType = $this->getModuleLoader()->getDataType();
		$analyse = $this->getGridAnalyserService()->checkRelationForTable($dataType);

		if (empty($analyse)) {
			$result = 'No relation involved in this Grid.';
		} else {
			$result = implode("\n", $analyse);
		}

		$view->assign('result', $result);
		$view->assign('dataType', $dataType);

		return $view->render();
	}

	/**
	 * Tell whether the tools should be displayed according to the context.
	 *
	 * @return bool
	 */
	public function isShown() {
		return $this->getBackendUser()->isAdmin();# && GeneralUtility::getApplicationContext()->isDevelopment();
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \Fab\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \Fab\Vidi\Grid\GridAnalyserService
	 */
	protected function getGridAnalyserService() {
		return GeneralUtility::makeInstance('Fab\Vidi\Grid\GridAnalyserService');
	}
}

