<?php
namespace Fab\Vidi\Tests\Unit\Formatter;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Fab\Vidi\Formatter\Date;
/**
 * Test case for class \Fab\Vidi\Formatter\Date.
 */
class DateTest extends UnitTestCase {

	/**
	 * @var Date
	 */
	private $subject;

	public function setUp() {
		date_default_timezone_set('GMT');
		$this->subject = new Date();
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] = 'd.m.Y';
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function canFormatDate() {
		$foo = $this->subject->format('1351880525');
		$this->assertEquals('02.11.2012', $foo);
	}
}