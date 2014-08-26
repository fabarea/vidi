<?php
namespace TYPO3\CMS\Vidi\Tool;

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
 * Interface for checking.
 */
interface ToolInterface {

	/**
	 * Display the title of the tool on the welcome screen.
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Display the description of the tool on the welcome screen.
	 *
	 * @return string
	 */
	public function getDescription();

	/**
	 * Do the job.
	 *
	 * @param array $arguments
	 * @return string
	 */
	public function work(array $arguments = array());

	/**
	 * Tell whether the tools should be displayed.
	 *
	 * @return bool
	 */
	public function isShown();

}
