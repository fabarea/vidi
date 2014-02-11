<?php
namespace TYPO3\CMS\Vidi\Domain\Validator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
use TYPO3\CMS\Vidi\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Exception\MissingUidException;

/**
 * Validate content to be updated
 * Beware this validator does not extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
 */
class ContentValidator {

	/**
	 * Check whether the Content is valid.
	 *
	 * @param array $content
	 * @param string $dataType
	 * @throws \TYPO3\CMS\Vidi\Exception\MissingUidException
	 * @throws \Exception
	 * @return void
	 */
	public function validate(array $content = array(), $dataType) {

		// Content Uid is mandatory
		if (empty($content['uid'])) {
			throw new MissingUidException('Missing Uid', 1351605545);
		}

		// Fetch the adequate repository
		$contentRepository = ContentRepositoryFactory::getInstance($dataType);

		// Makes sure the object exists in the database
		if ($contentRepository->findByUid($content['uid']) === FALSE) {
			$message = sprintf('I could not find object of type "%s" with identifier "%s". Can you double check?',
				$dataType,
				$content['uid']
			);
			throw new \Exception($message, 1390639895);
		}
	}
}
