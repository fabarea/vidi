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

/**
 * Test case for class \TYPO3\CMS\Vidi\Tca\TcaServiceFactory.
 */
class TcaServiceFactoryTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\CMS\Vidi\Tca\TcaServiceFactory
	 */
	private $fixture;

	public function setUp() {
	}

	public function tearDown() {
	}

	/**
	 * @test
	 */
	public function instantiateVariousFieldServicesForTableFeUsers() {
		$tableName = 'fe_users';
		foreach (array('field', 'table', 'grid', 'form') as $serviceType) {
			$fieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getService($tableName, $serviceType);
			$instanceName = sprintf('\TYPO3\CMS\Vidi\Tca\%sService', $serviceType);
			$this->assertTrue($fieldService instanceof $instanceName);
		}
	}

	/**
	 * @test
	 */
	public function instantiateVariousFieldServicesAndCheckWhetherTheClassInstanceIsStored() {
		$tableName = 'fe_users';
		foreach (array('field', 'table', 'grid') as $serviceType) {
			\TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getService($tableName, $serviceType);
			$instanceName = sprintf('\TYPO3\CMS\Vidi\Tca\%sService', $serviceType);
			$storage = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getInstanceStorage();
			$this->assertTrue($storage[$tableName][$serviceType] instanceof $instanceName);
		}
	}

	/**
	 * @test
	 */
	public function instantiateTableServicesForTableFeUsers() {
		$tableName = 'fe_users';
		$serviceType = 'table';
		$fieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getService($tableName, $serviceType);
		$instanceName = sprintf('\TYPO3\CMS\Vidi\Tca\%sService', $serviceType);
		$this->assertTrue($fieldService instanceof $instanceName);
	}

	/**
	 * @test
	 */
	public function instantiateGridServicesForTableFeUsers() {
		$tableName = 'fe_users';
		$serviceType = 'grid';
		$fieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getService($tableName, $serviceType);
		$instanceName = sprintf('\TYPO3\CMS\Vidi\Tca\%sService', $serviceType);
		$this->assertTrue($fieldService instanceof $instanceName);
	}

	/**
	 * @test
	 */
	public function instantiateFieldServicesForTableFeUsers() {
		$tableName = 'fe_users';
		$serviceType = 'field';
		$fieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getService($tableName, $serviceType);
		$instanceName = sprintf('\TYPO3\CMS\Vidi\Tca\%sService', $serviceType);
		$this->assertTrue($fieldService instanceof $instanceName);
	}

	/**
	 * @test
	 */
	public function instantiateFormServicesForTableFeUsers() {
		$tableName = 'fe_users';
		$serviceType = 'form';
		$fieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getService($tableName, $serviceType);
		$instanceName = sprintf('\TYPO3\CMS\Vidi\Tca\%sService', $serviceType);
		$this->assertTrue($fieldService instanceof $instanceName);
	}

}
?>