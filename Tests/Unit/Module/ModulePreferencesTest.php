<?php
namespace Fab\Vidi\Tests\Unit\Tca;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Fab\Vidi\Module\ModulePreferences;
/**
 * Test case for class \Fab\Vidi\Module\ModulePreferencesTest.
 */
class ModulePreferencesTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function instantiateMe() {
		$fixture = new ModulePreferences();
		$this->assertInstanceOf('Fab\Vidi\Module\ModulePreferences', $fixture);
	}

}
