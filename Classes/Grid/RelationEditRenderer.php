<?php
namespace TYPO3\CMS\Vidi\Grid;

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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for editing mm relation between objects.
 */
class RelationEditRenderer extends GridRendererAbstract {

	/**
	 * Render a representation of the relation on the GUI.
	 *
	 * @return string
	 */
	public function render() {

		$template = '<div style="text-align: right" class="pull-right invisible"><a href="%s" class="btn-edit-relation">%s</a></div>';

		// Initialize url parameters array.
		$urlParameters = array(
			$this->getModuleLoader()->getParameterPrefix() => array(
				'controller' => 'Content',
				'action' => 'edit',
				'matches' => array('uid' => $this->object->getUid()),
				'fieldNameAndPath' => $this->getFieldName(),
			),
		);

		$result = sprintf($template,
			$this->getModuleLoader()->getModuleUrl($urlParameters),
			IconUtility::getSpriteIcon('actions-edit-add')
		);

		return $result;
	}
}
