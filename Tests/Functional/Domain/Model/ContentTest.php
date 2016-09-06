<?php
namespace Fab\Vidi\Domain\Model;

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

use Fab\Vidi\Tests\Functional\AbstractFunctionalTestCase;

require_once dirname(dirname(dirname(__FILE__))) . '/AbstractFunctionalTestCase.php';

/**
 * Test case for class \Fab\Vidi\Domain\Model\Content.
 */
class ContentTest extends AbstractFunctionalTestCase {

	/**
	 * @var \Fab\Vidi\Domain\Model\Content
	 */
	private $fixture;

	/**
	 * @var string
	 */
	private $dataType = 'tx_foo_domain_model_bar';

	public function setUp() {
		parent::setUp();
		$GLOBALS['TCA'][$this->dataType]['columns'] = array(
			'foo' => [],
			'foo_bar' => [],
		);
		$this->fixture = new \Fab\Vidi\Domain\Model\Content($this->dataType);
	}

	public function tearDown() {
		unset($this->fixture, $GLOBALS['TCA'][$this->dataType]);
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function fieldNameIsConvertedToPropertyName($fieldName, $propertyName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$object = new \Fab\Vidi\Domain\Model\Content($this->dataType, $data);
		$this->assertObjectHasAttribute($propertyName, $object);
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function accessValueOfArrayObjectReturnsFooDataAsString($fieldName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$this->markTestIncomplete(); # TCA must be faked
		#$object = new \Fab\Vidi\Domain\Model\Content($this->dataType, $data);
		#$this->assertSame($data[$fieldName], $object[$fieldName]);
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function getValueThroughGetterReturnsFooDataAsString($fieldName, $propertyName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$this->markTestIncomplete(); # TCA must be faked
		#$object = new \Fab\Vidi\Domain\Model\Content($this->dataType, $data);
		#$getter = 'get' . ucfirst($propertyName);
		#$this->assertSame($data[$fieldName], $object->$getter());
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function toArrayMethodContainsGivenFieldName($fieldName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$object = new \Fab\Vidi\Domain\Model\Content($this->dataType, $data);
		$array = $object->toArray();
		$this->assertArrayHasKey($fieldName, $array);
	}

	/**
	 * Provider
	 */
	public function fieldNameProvider() {
		return array(
			array('foo', 'foo'),
			array('foo_bar', 'fooBar'),
		);
	}

	/**
	 * @test
	 * @dataProvider propertyProvider
	 */
	public function testProperty($propertyName, $value) {
		$setter = 'set' . ucfirst($propertyName);
		$getter = 'get' . ucfirst($propertyName);
		$this->markTestIncomplete(); # TCA must be faked
		#call_user_func_array(array($this->fixture, $setter), array($value));
		#$this->assertEquals($value, call_user_func(array($this->fixture, $getter)));
	}

	/**
	 * Provider
	 */
	public function propertyProvider() {
		return array(
			array('username', 'foo'),
		);
	}
}