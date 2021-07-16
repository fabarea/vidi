<?php
namespace Fab\Vidi\Backend;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\LanguageService;

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
        GeneralUtility::writeFileToTypo3tempDir($languageFile, $content);

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
        $languageDirectory = Environment::getPublicPath() . '/typo3temp/vidi';
        if (!is_dir($languageDirectory)) {
            GeneralUtility::mkdir($languageDirectory);
        }
        return $languageDirectory;
    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class, $GLOBALS['BE_USER']->uc['lang']);
    }

}
