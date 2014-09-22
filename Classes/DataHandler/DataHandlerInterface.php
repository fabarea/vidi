<?php
namespace TYPO3\CMS\Vidi\DataHandler;

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
use TYPO3\CMS\Vidi\Domain\Model\Content;

/**
 * Interface dealing with Data Handling.
 */
interface DataHandlerInterface {

	/**
	 * Process Content with action "update".
	 *
	 * @param Content $content
	 * @return bool
	 */
	public function processUpdate(Content $content);

	/**
	 * Process Content with action "remove".
	 *
	 * @param Content $content
	 * @return bool
	 */
	public function processRemove(Content $content);

	/**
	 * Process Content with action "copy".
	 *
	 * @param Content $content
	 * @param string $target
	 * @return bool
	 */
	public function processCopy(Content $content, $target);

	/**
	 * Process Content with action "move".
	 *
	 * @param Content $content
	 * @param string $target
	 * @return bool
	 */
	public function processMove(Content $content, $target);

	/**
	 * Process Content with action "localize".
	 *
	 * @param Content $content
	 * @param int $language
	 * @return bool
	 */
	public function processLocalize(Content $content, $language);

	/**
	 * Return error that have occurred while processing the data.
	 *
	 * @return array
	 */
	public function getErrorMessages();

}
