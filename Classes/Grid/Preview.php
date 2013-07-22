<?php
namespace TYPO3\CMS\Vidi\Grid;
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

/**
 * Class rendering the preview of a media in the grid
 */
class Preview implements \TYPO3\CMS\Vidi\Grid\GridRendererInterface {

	/**
	 * @var \TYPO3\CMS\Vidi\Service\ThumbnailService
	 */
	protected $thumbnailService;

	/**
	 * @var \TYPO3\CMS\Vidi\ViewHelpers\MetadataViewHelper
	 */
	protected $metadataViewHelper;

	/**
	 * @return \TYPO3\CMS\Vidi\Grid\Preview
	 */
	public function __construct() {
		$this->thumbnailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Service\ThumbnailService');
		$this->metadataViewHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ViewHelpers\MetadataViewHelper');
	}

	/**
	 * Render a preview of an media.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $asset
	 * @return string
	 */
	public function render(\TYPO3\CMS\Vidi\Domain\Model\Content $asset = NULL) {
		$result = $this->thumbnailService->setFile($asset)
			->setOutputType(\TYPO3\CMS\Vidi\Service\ThumbnailInterface::OUTPUT_IMAGE_WRAPPED)
			->setAppendTimeStamp(TRUE)
			->setTarget(\TYPO3\CMS\Vidi\Service\ThumbnailInterface::TARGET_BLANK)
			->create();

		$format = '%s K';
		$properties = array('size');

		if ($asset->getType() === \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE) {
			$format = '%s x %s';
			$properties = array('width', 'height');
		}

		$result .= $this->metadataViewHelper->render($asset, $format, $properties);
		return $result;
	}
}
?>