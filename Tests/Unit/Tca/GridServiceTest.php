<?php
namespace Fab\Vidi\Tests\Unit\Tca;

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

use Fab\Vidi\Formatter\Date;
use Fab\Vidi\Formatter\Datetime;
use Fab\Vidi\Tca\Tca;

/**
 * Test case for class \Fab\Vidi\Tca\GridService.
 */
class GridServiceTest extends AbstractServiceTest {

	/**
	 * @var \Fab\Vidi\Tca\GridService
	 */
	private $fixture;

	public function setUp() {
		parent::setUp();
		$this->fixture = $this->getMock('Fab\Vidi\Tca\GridService', array('getModulePreferences'), array('tx_foo', Tca::TYPE_GRID));

		// Configure the ModulePreferences
		$mockModulePreferences = $this->getMock('Fab\Vidi\Module\ModulePreferences');
		$mockModulePreferences->expects($this->once())->method('get')->will($this->returnValue(array()));
		$this->fixture->expects($this->once())->method('getModulePreferences')->will($this->returnValue($mockModulePreferences));
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getLabelReturnNameAsValue() {
		$GLOBALS['LANG'] = $this->getMock('TYPO3\CMS\Lang\LanguageService', array(), array(), '', FALSE);
		$GLOBALS['LANG']->expects($this->once())->method('sL')->will($this->returnValue('Name'));
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
	public function getColumnsReturnsAnNotEmptyArray() {
		$actual = $this->fixture->getFields();
		$this->assertTrue(is_array($actual));
		$this->assertNotEmpty($actual);
	}

	/**
	 * @test
	 */
	public function getFieldsReturnsGreaterThanNumberOfColumns() {
		$actual = $this->fixture->getFields();
		$this->assertGreaterThanOrEqual(count($actual), count($GLOBALS['TCA']['tx_foo']['columns']));
	}

	/**
	 * @test
	 */
	public function additionalFieldsAreHiddenByDefault() {
		$actual = $this->fixture->getFields();
		$this->assertFalse($actual['birthday']['visible']);
	}

	/**
	 * @test
	 */
	public function additionalFieldBirthDayIsFormattedAsDate() {
		$actual = $this->fixture->getFields();
		$this->assertEquals('Fab\Vidi\Formatter\Date', $actual['birthday']['format']);
	}

	/**
	 * @test
	 */
	public function additionalFieldStartTimeIsFormattedAsDateTime() {
		$actual = $this->fixture->getFields();
		$this->assertEquals('Fab\Vidi\Formatter\Datetime', $actual['starttime']['format']);
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
	public function additionalColumnFirstNameShouldNotBeVisible() {
		$actual = $this->fixture->isVisible('first_name');
		$this->assertFalse($actual);
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
		$this->assertFalse($this->fixture->isSortable('usergroup'));
	}

	/**
	 * @test
	 */
	public function getExcludedFieldsReturnsArray() {
		$result = $this->fixture->getExcludedFields();
		$this->assertInternalType('array', $result);
	}

	/**
	 * @test
	 */
	public function getFieldsRemoveFieldMiddleNameFromResultSet() {
		$result = $this->fixture->getFields();
		$this->assertArrayNotHasKey('middle_name', $result);
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
	public function getConfigurationOfNotExistingColumnReturnsAnException() {
		$expected = array();
		$this->assertEquals($expected, $this->fixture->getRenderers('bar'));
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

	/**
	 * @test
	 */
	public function canGetLabelKeyCodeForFakeFieldUserGroups() {
		$fieldName = 'usergroup';
		$this->assertEquals($GLOBALS['TCA']['tx_foo']['grid']['columns'][$fieldName]['label'], $this->fixture->getLabelKey($fieldName));
	}

}
