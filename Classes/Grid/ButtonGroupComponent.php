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

/**
 * Class for configuring a "Button Group" Grid Renderer.
 */
class ButtonGroupComponent extends GenericRendererComponent {

	/**
	 * Configure the "Button Group" Grid Renderer.
	 */
	public function __construct() {
		$configuration = array(
			'sortable' => FALSE,
			'canBeHidden' => FALSE,
			'width' => '70px',
		);
		$className = 'TYPO3\CMS\Vidi\Grid\ButtonGroupRenderer';
		parent::__construct($className, $configuration);
	}
}
