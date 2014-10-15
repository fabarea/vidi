<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Content;

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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Domain\Repository\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Persistence\Matcher;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which counts a result set.
 */
class CountViewHelper extends AbstractContentViewHelper {


	/**
	 * Count a result set.
	 *
	 * @return int
	 */
	public function render() {
		if (!empty($this->arguments['dataType'])) {
			print 'Sorry to be so rude! There is something to change in the View Helper "v:find". Please replace attribute "dataType" by "type". This is a shorter syntax...';
			exit();
		}
		$dataType = $this->arguments['type'];
		$matches = $this->replacesAliases($this->arguments['matches']);
		$ignoreEnableFields = $this->arguments['ignoreEnableFields'];

		$matcher = $this->getMatcher($dataType, $matches);

		$contentRepository = ContentRepositoryFactory::getInstance($dataType);
		$contentRepository->setDefaultQuerySettings($this->getDefaultQuerySettings($ignoreEnableFields));

		$numberOfObjects = ContentRepositoryFactory::getInstance($dataType)->countBy($matcher);
		return $numberOfObjects;
	}

}
