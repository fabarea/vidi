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
 * Class for configuring a Grid Renderer in the Grid TCA.
 */
class GenericRendererComponent implements GridComponentInterface {

	/**
	 * @var string
	 */
	protected $className;

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Constructor of a Generic component in Vidi.
	 *
	 * @param string $className
	 * @param array $configuration
	 */
	public function __construct($className, $configuration = array()) {
		$this->className = $className;
		$this->configuration = $configuration;
	}

	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return array
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array(
			'partial' => $this->getClassName(),
			'configuration' => $this->getConfiguration(),
		);
	}

	/**
	 * Magic method implementation for retrieving state.
	 *
	 * @param array $states
	 * @return GenericRendererComponent
	 */
	static public function __set_state($states) {
		return new GenericRendererComponent($states['className'], $states['configuration']);
	}
}
