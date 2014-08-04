<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Content;
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

		$dataType = $this->arguments['dataType'];
		$matches = $this->arguments['matches'];
		$ignoreEnableFields = $this->arguments['ignoreEnableFields'];

		$matcher = $this->getMatcher($dataType, $matches);

		$contentRepository = ContentRepositoryFactory::getInstance($dataType);
		$contentRepository->setDefaultQuerySettings($this->getDefaultQuerySettings($ignoreEnableFields));

		$numberOfObjects = ContentRepositoryFactory::getInstance($dataType)->countBy($matcher);
		return $numberOfObjects;
	}

}
