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
 * Class for configuring a "Edit Relation" Grid Renderer in the Grid TCA.
 */
class RelationEditComponent extends GenericRendererComponent {

	/**
	 * Constructor for a "Edit Relation" Grid Renderer Component.
	 */
	public function __construct() {
		$className = 'TYPO3\CMS\Vidi\Grid\RelationEditRenderer';
		parent::__construct($className, array());
	}
}
