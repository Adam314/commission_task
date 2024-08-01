<?php

namespace Adam314\CommissionTask\Tests\Util;

use Adam314\CommissionTask\Util\Date;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    public static function getWeekBoundariesDataProvider(): array
    {
        return [
            // wednesday, find monday and sunday
            ['2024-07-31', '2024-07-29', '2024-08-04'],
            // monday, find sunday
            ['2024-07-29', '2024-07-29', '2024-08-04'],
            // sunday, find monday
            ['2024-08-04', '2024-07-29', '2024-08-04']
        ];
    }

    /**
     * @dataProvider getWeekBoundariesDataProvider
     */
    public function testGetWeekBoundaries(string $day, string $expectedMonday, string $expectedSunday): void
    {
        $result = Date::getWeekBoundaries(new \DateTime($day));

        $this->assertIsArray($result);

        $this->assertEquals(new \DateTime($expectedMonday), $result[0]);
        $this->assertEquals(new \DateTime($expectedSunday), $result[1]);
    }
}
