<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Tca;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which returns the title of a content object.
 */
class TitleViewHelper extends AbstractViewHelper {

	/**
	 * Returns the title of a content object.
	 *
	 * @param Content $content
	 * @return string
	 */
	public function render(Content $content) {
		$table = TcaService::table($content->getDataType());
		return $content[$table->getLabelField()];
	}

}
