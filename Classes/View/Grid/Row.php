<?php
namespace TYPO3\CMS\Vidi\View\Grid;

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
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Language\LanguageService;
use TYPO3\CMS\Vidi\Language\LocalizationStatus;
use TYPO3\CMS\Vidi\Tca\TcaService;
use TYPO3\CMS\Vidi\View\AbstractComponentView;

/**
 * View helper for rendering a row of a content object.
 */
class Row extends AbstractComponentView {

	/**
	 * @var array
	 */
	protected $columns = array();

	/**
	 * Registry for storing variable values and speed up the processing.
	 *
	 * @var array
	 */
	protected $variables = array();

	/**
	 * @param array $columns
	 */
	public function __construct($columns = array()){
		$this->columns = $columns;
	}

	/**
	 * Render a row to be displayed in the Grid given an Content Object.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param int $rowIndex
	 * @return array
	 */
	public function render(Content $object = NULL, $rowIndex = 0) {

		// Initialize returned array
		$output = array();

		foreach(TcaService::grid()->getFields() as $fieldNameAndPath => $configuration) {

			$value = ''; // default is empty at first.

			$this->computeVariables($object, $fieldNameAndPath);

			// Only compute the value if it is going to be shown in the Grid. Lost time otherwise!
			if (in_array($fieldNameAndPath, $this->columns)) {

				// Fetch value
				if (TcaService::grid()->hasRenderers($fieldNameAndPath)) {

					$value = '';
					$renderers = TcaService::grid()->getRenderers($fieldNameAndPath);

					// if is relation has one
					foreach ($renderers as $rendererClassName => $rendererConfiguration) {

						/** @var $rendererObject \TYPO3\CMS\Vidi\Grid\GridRendererInterface */
						$rendererObject = GeneralUtility::makeInstance($rendererClassName);
						$value .= $rendererObject
							->setObject($object)
							->setFieldName($fieldNameAndPath)
							->setRowIndex($rowIndex)
							->setFieldConfiguration($configuration)
							->setGridRendererConfiguration($rendererConfiguration)
							->render();
					}
				} else {
					$value = $this->resolveValue($object, $fieldNameAndPath);
					$value = $this->processValue($value, $object, $fieldNameAndPath); // post resolve processing.
				}

				// Possible formatting given by configuration. @see TCA['grid']
				$value = $this->formatValue($value, $configuration);

				// Here, there is the chance to further "decorate" the value for inline editing, localization, ...
				if ($this->willBeEnriched($value, $object, $fieldNameAndPath)) {

					$localizedStructure = $this->initializeLocalizedStructure($value);

					if ($this->isEditable()) {
						$localizedStructure = $this->addEditableMarkup($localizedStructure);
					}

					if ($this->isLocalized()) {
						$localizedStructure = $this->addLocalizationMarkup($localizedStructure);
					}

					if ($this->hasIcon()) {
						$localizedStructure = $this->addSpriteIconMarkup($localizedStructure);
					}

					$value = $this->flattenStructure($localizedStructure);
				}


				// Final wrap given by configuration. @see TCA['grid']
				$value = $this->wrapValue($value, $configuration);
			}

			$output[$this->getFieldName()] = $value;
		}

		$output['DT_RowId'] = 'row-' . $object->getUid();
		$output['DT_RowClass'] = sprintf('%s_%s', $object->getDataType(), $object->getUid());

		return $output;
	}

	/**
	 * Flatten the localized structure to render the final value
	 *
	 * @param array $localizedStructure
	 * @return array
	 */
	protected function flattenStructure(array $localizedStructure) {

		// Flatten the structure.
		$value = '';
		foreach ($localizedStructure as $structure) {
			$value .= sprintf('<div class="%s">%s</div>',
				$structure['status'] !== LocalizationStatus::LOCALIZED ? 'invisible' : '',
				$structure['value']
			);
		}
		return $value;
	}

	/**
	 * Store some often used variable values and speed up the processing.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param string $fieldNameAndPath
	 * @return array
	 */
	protected function computeVariables(Content $object, $fieldNameAndPath) {
		$this->variables = array();
		$this->variables['dataType'] = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
		$this->variables['fieldName'] = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);
		$this->variables['fieldNameAndPath'] = $fieldNameAndPath;
		$this->variables['object'] = $object;
	}

	/**
	 * Tell whether the object will be decorated / wrapped such as
	 *
	 * @param string $value
	 * @return array
	 */
	protected function initializeLocalizedStructure($value) {

		$localizedStructure[] = array(
			'value' => empty($value) && $this->isEditable() ?  $this->getEmptyValuePlaceholder() : $value,
			'status' => empty($value) ? LocalizationStatus::EMPTY_VALUE : LocalizationStatus::LOCALIZED,
			'language' => 0,
			'languageFlag' => $defaultLanguage = $this->getLanguageService()->getDefaultFlag(),
		);

		if ($this->isLocalized()) {

			foreach ($this->getLanguageService()->getLanguages() as $language) {
				$resolvedObject = $this->getResolvedObject();
				$fieldName = $this->getFieldName();

				if ($this->getLanguageService()->hasLocalization($resolvedObject, $language['uid'])) {
					$localizedValue = $this->getLanguageService()->getLocalizedFieldName($resolvedObject, $language['uid'], $fieldName);
					$status = LocalizationStatus::LOCALIZED;

					// Replace blank value by something more meaningful for the End User.
					if (empty($localizedValue)) {
						$status = LocalizationStatus::EMPTY_VALUE;
						$localizedValue = $this->isEditable() ? $this->getEmptyValuePlaceholder() : '';
					}
				} else {
					$localizedValue = sprintf('<a href="%s" style="color: black">%s</a>',
						$this->getLocalizedUri($language['uid']),
						$this->getLabelService()->sL('LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:create_translation')
					);
					$status = LocalizationStatus::NOT_YET_LOCALIZED;
				}

				// Feed structure.
				$localizedStructure[] = array(
					'value' => $localizedValue,
					'status' => $status,
					'language' => (int)$language['uid'],
					'languageFlag' => $language['flag'],
				);
			}
		}

		return $localizedStructure;
	}

	/**
	 * Returns a placeholder when the value is empty.
	 *
	 * @return string
	 */
	protected function getEmptyValuePlaceholder() {
		return sprintf('<i>%s</i>',
			$this->getLabelService()->sL('LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:start_editing')
		);
	}

	/**
	 * Tell whether the object will be decorated (or wrapped) for inline editing, localization purpose.
	 *
	 * @return bool
	 */
	protected function willBeEnriched() {

		$willBeEnriched = FALSE;

		if ($this->fieldExists()) {
			$willBeEnriched = $this->isEditable() || $this->hasIcon() || $this->isLocalized();
		}

		return $willBeEnriched;
	}

	/**
	 * Tell whether the field in the context will be prepended by an icon.
	 *
	 * @return bool
	 */
	protected function hasIcon() {
		$dataType = $this->getDataType();
		return TcaService::table($dataType)->getLabelField() === $this->getFieldName();
	}

	/**
	 * Tell whether the field in the context will be prepended by an icon.
	 *
	 * @return bool
	 */
	protected function isLocalized() {
		$object = $this->getObject();
		$fieldName = $this->getFieldName();
		$dataType = $this->getDataType();
		$fieldNameAndPath = $this->getFieldNameAndPath();

		return $this->getLanguageService()->hasLanguages()
			&& TcaService::grid($object)->isLocalized($fieldNameAndPath)
			&& TcaService::table($dataType)->field($fieldName)->isLocalized();
	}

	/**
	 * Add some markup to have the content editable in the Grid.
	 *
	 * @param array $localizedStructure
	 * @return array
	 */
	protected function addEditableMarkup(array $localizedStructure) {

		$dataType = $this->getDataType();
		$fieldName = $this->getFieldName();

		foreach ($localizedStructure as $index => $structure) {
			if ($structure['status'] !== LocalizationStatus::NOT_YET_LOCALIZED) {
				$localizedStructure[$index]['value'] = sprintf('<span class="%s" data-language="%s">%s</span>',
					TcaService::table($dataType)->field($fieldName)->isTextArea()  ? 'editable-textarea' : 'editable-textfield',
					$structure['language'],
					$structure['value']
				);
			}
		}
		return $localizedStructure;
	}

	/**
	 * Add some markup related to the localization.
	 *
	 * @param array $localizedStructure
	 * @return array
	 */
	protected function addLocalizationMarkup(array $localizedStructure) {

		foreach ($localizedStructure as $index => $structure) {

			$localizedStructure[$index]['value'] = sprintf('<span>%s %s</span>',
				empty($structure['languageFlag']) ? '' : IconUtility::getSpriteIcon('flags-' . $structure['languageFlag']),
				$structure['value']
			);
		}
		return $localizedStructure;
	}

	/**
	 * Add some markup related to the prepended icon.
	 *
	 * @param array $localizedStructure
	 * @return array
	 */
	protected function addSpriteIconMarkup(array $localizedStructure) {

		$object = $this->getObject();

		foreach ($localizedStructure as $index => $structure) {

			$recordData = array();

			$enablesMethods = array('Hidden', 'Deleted', 'StartTime', 'EndTime');
			foreach ($enablesMethods as $enableMethod) {

				$methodName = 'get' . $enableMethod . 'Field';

				// Fetch possible hidden filed.
				$enableField = TcaService::table($object)->$methodName();
				if ($enableField) {
					$recordData[$enableField] = $object[$enableField];
				}
			}

			// Get Enable Fields of the object to render the sprite with overlays.
			$localizedStructure[$index]['value'] = sprintf('%s %s',
				IconUtility::getSpriteIconForRecord($object->getDataType(), $recordData),
				$structure['value']
			);
		}

		return $localizedStructure;
	}

	/**
	 * Return whether the field given by the context is editable.
	 *
	 * @return boolean
	 */
	protected function isEditable() {
		$fieldNameAndPath = $this->getFieldNameAndPath();
		$dataType = $this->getDataType();
		$fieldName = $this->getFieldName();

		return TcaService::grid()->isEditable($fieldNameAndPath)
			&& TcaService::table($dataType)->hasField($fieldName)
			&& TcaService::table($dataType)->field($fieldName)->hasNoRelation(); // relation are editable through Renderers only.
	}

	/**
	 * Return the appropriate URI to create the translation.
	 *
	 * @param int $language
	 * @return string
	 */
	protected function getLocalizedUri($language) {

		$additionalParameters = array(
			$this->getModuleLoader()->getParameterPrefix() => array(
				'controller' => 'Content',
				'action' => 'localize',
				'format' => 'json',
				'fieldNameAndPath' => $this->getFieldNameAndPath(),
				'language' => $language,
				'matches' => array(
					'uid' => $this->getObject()->getUid(),
				),
			),
		);
		return $this->getModuleLoader()->getModuleUrl($additionalParameters);
	}

	/**
	 * Compute the value for the Content object according to a field name.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param string $fieldNameAndPath
	 * @return string
	 */
	protected function resolveValue(Content $object, $fieldNameAndPath) {

		// Get the first part of the field name and
		$fieldName = $this->getFieldPathResolver()->stripFieldName($fieldNameAndPath);

		$value = $object[$fieldName];

		// Relation but contains no data.
		if (is_array($value) && empty($value)) {
			$value = '';
		} elseif ($value instanceof Content) {

			$fieldNameOfForeignTable = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

			// TRUE means the field name does not contains a path. "title" vs "metadata.title"
			// Fetch the default label
			if ($fieldNameOfForeignTable === $fieldName) {
				$foreignTable = TcaService::table($object->getDataType())->field($fieldName)->getForeignTable();
				$fieldNameOfForeignTable = TcaService::table($foreignTable)->getLabelField();
			}

			$value = $object[$fieldName][$fieldNameOfForeignTable];
		}

		return $value;
	}

	/**
	 * Check whether a string contains HTML tags.
	 *
	 * @param string $string the content to be analyzed
	 * @return boolean
	 */
	protected function hasHtml($string) {
		$result = FALSE;

		// We compare the length of the string with html tags and without html tags.
		if (strlen($string) != strlen(strip_tags($string))) {
			$result = TRUE;
		}
		return $result;
	}

	/**
	 * Check whether a string contains potential XSS.
	 *
	 * @param string $string the content to be analyzed
	 * @return boolean
	 */
	protected function isClean($string) {

		// @todo implement me!
		$result = TRUE;
		return $result;
	}

	/**
	 * Process the value
	 *
	 * @todo implement me as a processor chain to be cleaner implementation wise. Look out at the performance however!
	 *       e.g DefaultValueGridProcessor, TextAreaGridProcessor, ...
	 *
	 * @param string $value
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param string $fieldNameAndPath
	 * @return string
	 */
	protected function processValue($value, Content $object, $fieldNameAndPath) {

		// Set default value if $field name correspond to the label of the table
		$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);
		if (TcaService::table($object->getDataType())->getLabelField() === $fieldName && empty($value)) {
			$value = sprintf('[%s]', $this->getLabelService()->sL('LLL:EXT:lang/locallang_core.xlf:labels.no_title', 1));
		}

		// Resolve the identifier in case of "select" or "radio button".
		$fieldType = TcaService::table($object->getDataType())->field($fieldNameAndPath)->getType();
		if ($fieldType !== TcaService::TEXTAREA) {
			$value = htmlspecialchars($value);
		} elseif ($fieldType === TcaService::TEXTAREA && !$this->isClean($value)) {
			$value = htmlspecialchars($value); // Avoid bad surprise, converts characters to HTML.
		} elseif ($fieldType === TcaService::TEXTAREA && !$this->hasHtml($value)) {
			$value = nl2br($value);
		}

		return $value;
	}


	/**
	 * Possible value formatting.
	 *
	 * @param string $value
	 * @param array $configuration
	 * @return string
	 */
	protected function formatValue($value, array $configuration) {
		if (empty($configuration['format'])) {
			return $value;
		}
		$className = $configuration['format'];

		/** @var \TYPO3\CMS\Vidi\Formatter\FormatterInterface $formatter */
		$formatter = GeneralUtility::makeInstance($className);
		$value = $formatter->format($value);

		return $value;
	}

	/**
	 * Possible value wrapping.
	 *
	 * @param string $value
	 * @param array $configuration
	 * @return string
	 */
	protected function wrapValue($value, array $configuration) {
		if (!empty($configuration['wrap'])) {
			$parts = explode('|', $configuration['wrap']);
			$value = implode($value, $parts);
		}
		return $value;
	}

	/**
	 * Tell whether the field in the context really exists.
	 *
	 * @return bool
	 */
	protected function fieldExists() {
		if (is_null($this->variables['hasField'])) {
			$dataType = $this->getDataType();
			$fieldName = $this->getFieldName();
			$this->variables['hasField'] = TcaService::table($dataType)->hasField($fieldName);
		}
		return $this->variables['hasField'];
	}

	/**
	 * @return string
	 */
	protected function getDataType() {
		return $this->variables['dataType'];
	}

	/**
	 * @return string
	 */
	protected function getFieldName() {
		return $this->variables['fieldName'];
	}

	/**
	 * @return string
	 */
	protected function getFieldNameAndPath() {
		return $this->variables['fieldNameAndPath'];
	}

	/**
	 * @return Content
	 */
	protected function getObject() {
		return $this->variables['object'];
	}

	/**
	 * @return Content
	 */
	protected function getResolvedObject() {
		if (empty($this->variables['resolvedObject'])) {
			$object = $this->getObject();
			$fieldNameAndPath = $this->getFieldNameAndPath();
			$this->variables['resolvedObject'] = $this->getContentObjectResolver()->getObject($object, $fieldNameAndPath);
		}
		return $this->variables['resolvedObject'];
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}
	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\ContentObjectResolver
	 */
	protected function getContentObjectResolver () {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\ContentObjectResolver');
	}

	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLabelService() {
		return $GLOBALS['LANG'];
	}

	/**
	 * @return LanguageService
	 */
	protected function getLanguageService() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Language\LanguageService');
	}

}
