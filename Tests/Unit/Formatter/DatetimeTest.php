<?php

namespace Fab\Vidi\Tests\Unit\Formatter;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Fab\Vidi\Formatter\Date;
use Fab\Vidi\Formatter\Datetime;

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
/**
 * Test case for class \Fab\Vidi\Formatter\Datetime.
 */
class DatetimeTest extends UnitTestCase
{
    /**
     * @var Date
     */
    private $subject;

    public function setUp()
    {
        date_default_timezone_set('GMT');
        $this->subject = new Datetime();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] = 'd.m.Y';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'] = 'H:i';
    }

    public function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function canFormatDatetime()
    {
        $foo = $this->subject->format('1351880525');
        $this->assertEquals('02.11.2012 18:22', $foo);
    }
}
