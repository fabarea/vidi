<?php
namespace Fab\Vidi\Grid;

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

use Fab\Vidi\Domain\Model\Content;
use TYPO3\CMS\Backend\Utility\IconUtility;
use Fab\Vidi\Tca\Tca;

/**
 * Class rendering visibility for the Grid.
 */
class VisibilityRenderer extends ColumnRendererAbstract {

	/**
	 * Render visibility for the Grid.
	 *
	 * @return string
	 */
	public function render() {

		$output = '';
		$hiddenField = Tca::table()->getHiddenField();

		if ($hiddenField) {

			$spriteName = $this->object[$hiddenField] ? 'actions-edit-unhide' : 'actions-edit-hide';
			$output = sprintf(
				'<a href="%s" class="btn-visibility-toggle" title="%s">%s</a>',
				$this->getEditUri($this->object),
				$this->getLabelService()->sL('LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:visibility_renderer.toggle'),
				IconUtility::getSpriteIcon($spriteName)
			);
		}
		return $output;
	}

	/**
	 * @param Content $object
	 * @return string
	 */
	protected function getEditUri(Content $object) {
		$additionalParameters = array(
			$this->getModuleLoader()->getParameterPrefix() => array(
				'controller' => 'Content',
				'action' => 'update',
				'format' => 'json',
				'fieldNameAndPath' => Tca::table()->getHiddenField(),
				'matches' => array(
					'uid' => $object->getUid(),
				),
				'content' => array(Tca::table()->getHiddenField() => 'PLACEHOLDER')
			),
		);

		return $this->getModuleLoader()->getModuleUrl($additionalParameters);
	}

	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLabelService() {
		return $GLOBALS['LANG'];
	}

}
