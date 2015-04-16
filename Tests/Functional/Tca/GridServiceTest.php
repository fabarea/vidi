<?php
namespace TYPO3\CMS\Vidi\Tca;

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
 * Test case for class \TYPO3\CMS\Vidi\Tca\GridService.
 */
class GridServiceTest extends \TYPO3\CMS\Vidi\Tests\Functional\AbstractFunctionalTestCase {

	/**
	 * @var \TYPO3\CMS\Vidi\Tca\GridService
	 */
	private $fixture;

	public function setUp() {
		parent::setUp();
		$tableName = 'fe_users';
		$serviceType = 'grid';
		$this->fixture = new \TYPO3\CMS\Vidi\Tca\GridService($tableName, $serviceType);

		// create language-object
		global $LANG;
		$LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('language');
		$LANG->init('default');
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getLabelReturnNameAsValue() {
		$this->assertEquals('Name', $this->fixture->getLabel('name'));
	}

	/**
	 * @test
	 */
	public function getFieldNamesReturnsNotEmpty() {
		$actual = $this->fixture->getFieldNames();

		$this->assertTrue(is_array($actual));
		$this->assertNotEmpty($actual);
		$this->assertTrue(in_array('username', $actual));
	}

	/**
	 * @test
	 */
	public function getColumnsReturnsNotEmpty() {
		$actual = $this->fixture->getFields();
		$this->assertTrue(is_array($actual));
		$this->assertNotEmpty($actual);
	}

	/**
	 * @test
	 */
	public function getConfigurationForColumnUsername() {
		$actual = $this->fixture->getField('username');
		$this->assertTrue(is_array($actual));
		$this->assertTrue(count($actual) > 0);
	}

	/**
	 * @test
	 */
	public function labelOfColumnUsernameShouldBeUsernameByDefault() {
		$this->assertEquals('Username', $this->fixture->getLabel('username'));
	}

	/**
	 * @test
	 */
	public function labelOfColumnFooShouldBeEmpty() {
		$this->markTestIncomplete(); # TCA must be faked
		#$this->assertEmpty($this->fixture->getLabel(uniqid('foo_')));
	}

	/**
	 * @test
	 */
	public function columnUsernameShouldBeSortableByDefault() {
		$this->assertTrue($this->fixture->isSortable('username'));
	}

	/**
	 * @test
	 */
	public function columnNumberShouldBeNotSortableByDefault() {
		$this->assertFalse($this->fixture->isSortable('__buttons'));
	}

	/**
	 * @test
	 */
	public function columnUsernameShouldBeVisibleByDefault() {
		$this->assertTrue($this->fixture->isVisible('username'));
	}

	/**
	 * @test
	 */
	public function columnFooHasNoRenderer() {
		$this->assertFalse($this->fixture->hasRenderers(uniqid('foo')));
	}

	/**
	 * @test
	 */
	public function getTheRendererOfColumnFooIsEmptyArray() {
		$expected = array();
		$this->assertEquals($expected, $this->fixture->getRenderers(uniqid('foo')));
	}

	/**
	 * @test
	 */
	public function getFieldsAndCheckWhetherItsPositionReturnsTheCorrectFieldName() {
		$fields = array_keys($this->fixture->getFields());
		for ($index = 0; $index < count($fields); $index++) {
			$actual = $this->fixture->getFieldNameByPosition($index);
			$this->assertSame($fields[$index], $actual);
		}
	}

}
?>