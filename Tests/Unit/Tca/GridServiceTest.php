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
		$this->fixture = new \Fab\Vidi\Tca\GridService('tx_foo', Tca::TYPE_GRID);
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
