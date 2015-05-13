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

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Test case for class \Fab\Vidi\Module\ModulePreferencesTest.
 */
class ModulePreferencesTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function instantiateMe() {
		$fixture = new \Fab\Vidi\Module\ModulePreferences();
		$this->assertInstanceOf('Fab\Vidi\Module\ModulePreferences', $fixture);
	}

}
