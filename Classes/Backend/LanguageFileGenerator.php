<?php
namespace Fab\Vidi\Backend;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Display custom fields in the Extension Manager.
 */
class LanguageFileGenerator implements SingletonInterface
{

    protected $template = '<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.0">
	<file source-language="en" datatype="plaintext" original="messages" date="" product-name="local lang module">
		<header/>
		<body>
			<trans-unit id="mlang_labels_tablabel">
				<source>{module_name}</source>
			</trans-unit>
			<trans-unit id="mlang_tabs_tab" xml:space="preserve">
				<source>{module_name}</source>
			</trans-unit>
			<trans-unit id="mlang_labels_tabdescr" xml:space="preserve">
				<source>{module_name}</source>
			</trans-unit>
		</body>
	</file>
</xliff>';

    /**
     * @param string $dataType
     * @return string
     */
    public function generate($dataType)
    {
        $label = $dataType;
        if (!empty($GLOBALS['TCA'][$dataType]['ctrl']['title'])) {
            $label = $this->getLanguageService()->sL($GLOBALS['TCA'][$dataType]['ctrl']['title']);
        }

        // Generate language file.
        $languageFile = $this->getLanguageFile($dataType);
        $content = str_replace('{module_name}', $label, $this->template);
        file_put_contents($languageFile, $content);

        return 'LLL:' . $languageFile;
    }

    /**
     * @param $dataType
     * @return string
     */
    protected function getLanguageFile($dataType)
    {
        return $this->getLanguageDirectory() . '/' . $dataType . '.xlf';
    }

    /**
     * @return string
     */
    protected function getLanguageDirectory()
    {
        // Create language file dynamically
        $languageDirectory = PATH_site . 'typo3temp/vidi';
        if (!is_dir($languageDirectory)) {
            GeneralUtility::mkdir($languageDirectory);
        }
        return $languageDirectory;
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class, $GLOBALS['BE_USER']->uc['lang']);
    }

}
