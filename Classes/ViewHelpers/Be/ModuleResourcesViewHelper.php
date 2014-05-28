<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Be;

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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

/**
 * Load resources from the Moduler Loader.
 */
class ModuleResourcesViewHelper extends AbstractBackendViewHelper {

	/**
	 * Return the number of the transaction with the client
	 *
	 * @return void
	 * @api
	 */
	public function render() {

		$doc = $this->getDocInstance();
		$pageRenderer = $doc->getPageRenderer();

		/** @var \TYPO3\CMS\Vidi\ModuleLoader $moduleLoader */
		$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\ModuleLoader');

		/** @var \TYPO3\CMS\Fluid\ViewHelpers\Uri\ResourceViewHelper $resourceViewHelper */
		$resourceViewHelper = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\ViewHelpers\Uri\ResourceViewHelper');

		foreach ($moduleLoader->getAdditionalStyleSheetFiles() as $addCssFile) {
			$fileNameAndPath = $this->resolvePath($addCssFile);
			$pageRenderer->addCssFile($fileNameAndPath);
		}

		foreach ($moduleLoader->getAdditionalJavaScriptFiles() as $addJsFile) {
			$fileNameAndPath = $this->resolvePath($addJsFile);
			$pageRenderer->addJsFile($fileNameAndPath);
		}
	}

	/**
	 * Resolve a resource path.
	 *
	 * @param string $uri
	 * @return string
	 */
	 protected function resolvePath($uri) {
		$uri = GeneralUtility::getFileAbsFileName($uri);
		$uri = substr($uri, strlen(PATH_site));
		if (TYPO3_MODE === 'BE' && $uri !== FALSE) {
			$uri = '../' . $uri;
		}
		return $uri;
	}
}
