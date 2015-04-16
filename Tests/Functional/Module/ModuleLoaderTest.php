<?php
namespace TYPO3\CMS\Vidi;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

require_once dirname(dirname(__FILE__)) . '/AbstractFunctionalTestCase.php';

/**
 * Test case for class \TYPO3\CMS\Vidi\Module\ModuleLoader.
 */
class ModuleLoaderTest extends \TYPO3\CMS\Vidi\Tests\Functional\AbstractFunctionalTestCase {

	/**
	 * @var \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	private $fixture;

	/**
	 * @var string
	 */
	private $dataType = 'tx_foo';

	/**
	 * @var string
	 */
	private $moduleCode = 'user_VidiTxFooM1';


	public function setUp() {
		parent::setUp();
		$this->fixture = new \TYPO3\CMS\Vidi\Module\ModuleLoader($this->dataType);
		$this->fixture->register();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 * @dataProvider attributeValueProvider
	 */
	public function attributeCanBeSet($attribute, $value) {
		$setter = 'set' . ucfirst($attribute);
		$this->fixture->$setter($value);
		$this->assertAttributeEquals($value, $attribute, $this->fixture);
	}

	/**
	 * Provider
	 */
	public function attributeValueProvider() {
		return array(
			array('icon', 'bar'),
			array('moduleLanguageFile', 'bar'),
		);
	}

	/**
	 * @test
	 * @dataProvider attributeProvider
	 */
	public function testAttribute($attribute, $defaultValue) {
		$this->assertAttributeEquals($defaultValue, $attribute, $this->fixture);
	}

	/**
	 * Provider
	 */
	public function attributeProvider() {
		return array(
			array('dataType', $this->dataType),
			array('moduleKey', 'm1'),
			array('icon', 'EXT:vidi/ext_icon.gif'),
			array('moduleLanguageFile', 'LLL:EXT:vidi/Resources/Private/Language/locallang_module.xlf'),
		);
	}

	/**
	 * @test
	 */
	public function getModuleConfigurationReturnsArrayWithSomeKeys() {
		$moduleLoader = new \TYPO3\CMS\Vidi\Module\ModuleLoader($this->dataType);
		$moduleLoader->register();
		$GLOBALS['_GET']['M'] = $this->moduleCode;

		$moduleConfiguration = $moduleLoader->getModuleConfiguration();
		$keys = array('dataType', 'additionalJavaScriptFiles', 'additionalStyleSheetFiles');
		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, $moduleConfiguration);
		}
	}

	/**
	 * @test
	 */
	public function getModuleConfigurationWithParameterDataTypeReturnsDataType() {
		$moduleLoader = new \TYPO3\CMS\Vidi\Module\ModuleLoader($this->dataType);
		$moduleLoader->register();
		$GLOBALS['_GET']['M'] = $this->moduleCode;
		$this->assertEquals($this->dataType, $moduleLoader->getModuleConfiguration('dataType'));
	}
}
?>