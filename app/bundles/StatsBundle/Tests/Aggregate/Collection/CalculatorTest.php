<?php


namespace Mautic\StatsBundle\Tests\Aggregate\Collection;

use Mautic\StatsBundle\Aggregate\Calculator;
use Mautic\StatsBundle\Aggregate\Collection\DAO\StatsDAO;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function testSumByYearReturnsExpectedCount()
    {
        $expected = [
            2018 => 600,
            2019 => 300,
        ];

        $this->assertEquals($expected, $this->getCalculator()->getSumsByYear()->getStats());
    }

    public function testSumByMonthReturnsExpectedCount()
    {
        $expected = [
            1   => 0,
            2   => 0,
            3   => 0,
            4   => 0,
            5   => 0,
            6   => 0,
            7   => 0,
            8   => 0,
            9   => 0,
            10  => 0,
            11  => 200,
            12  => 700,
        ];

        $this->assertEquals($expected, $this->getCalculator()->getSumsByMonth('n')->getStats());

        $expected = [
            '2018-12' => 600,
            '2019-01' => 0,
            '2019-02' => 0,
            '2019-03' => 0,
            '2019-04' => 0,
            '2019-05' => 0,
            '2019-06' => 0,
            '2019-07' => 0,
            '2019-08' => 0,
            '2019-09' => 0,
            '2019-10' => 0,
            '2019-11' => 200,
            '2019-12' => 100,
        ];

        $this->assertEquals($expected, $this->getCalculator()->getSumsByMonth('Y-m')->getStats());
    }

    public function testSumByDayReturnsExpectedCount()
    {
        $expected = [
            7  => 500,
            8  => 400,
            9  => 0,
            10 => 0,
            11 => 0,
            12 => 0,
            13 => 0,
            14 => 0,
            15 => 0,
            16 => 0,
            17 => 0,
            18 => 0,
            19 => 0,
            20 => 0,
            21 => 0,
            22 => 0,
            23 => 0,
            24 => 0,
            25 => 0,
            26 => 0,
            27 => 0,
            28 => 0,
            29 => 0,
            30 => 0,
            31 => 0,
            1  => 0,
            2  => 0,
            3  => 0,
            4  => 0,
            5  => 0,
            6  => 0,
        ];

        $this->assertEquals($expected, $this->getCalculator()->getSumsByDay('j')->getStats());

        $expected = [
            '2018-12-07' => 300,
            '2018-12-08' => 300,
            '2018-12-09' => 0,
            '2018-12-10' => 0,
            '2018-12-11' => 0,
            '2018-12-12' => 0,
            '2018-12-13' => 0,
            '2018-12-14' => 0,
            '2018-12-15' => 0,
            '2018-12-16' => 0,
            '2018-12-17' => 0,
            '2018-12-18' => 0,
            '2018-12-19' => 0,
            '2018-12-20' => 0,
            '2018-12-21' => 0,
            '2018-12-22' => 0,
            '2018-12-23' => 0,
            '2018-12-24' => 0,
            '2018-12-25' => 0,
            '2018-12-26' => 0,
            '2018-12-27' => 0,
            '2018-12-28' => 0,
            '2018-12-29' => 0,
            '2018-12-30' => 0,
            '2018-12-31' => 0,
            '2019-01-01' => 0,
            '2019-01-02' => 0,
            '2019-01-03' => 0,
            '2019-01-04' => 0,
            '2019-01-05' => 0,
            '2019-01-06' => 0,
            '2019-01-07' => 0,
            '2019-01-08' => 0,
            '2019-01-09' => 0,
            '2019-01-10' => 0,
            '2019-01-11' => 0,
            '2019-01-12' => 0,
            '2019-01-13' => 0,
            '2019-01-14' => 0,
            '2019-01-15' => 0,
            '2019-01-16' => 0,
            '2019-01-17' => 0,
            '2019-01-18' => 0,
            '2019-01-19' => 0,
            '2019-01-20' => 0,
            '2019-01-21' => 0,
            '2019-01-22' => 0,
            '2019-01-23' => 0,
            '2019-01-24' => 0,
            '2019-01-25' => 0,
            '2019-01-26' => 0,
            '2019-01-27' => 0,
            '2019-01-28' => 0,
            '2019-01-29' => 0,
            '2019-01-30' => 0,
            '2019-01-31' => 0,
            '2019-02-01' => 0,
            '2019-02-02' => 0,
            '2019-02-03' => 0,
            '2019-02-04' => 0,
            '2019-02-05' => 0,
            '2019-02-06' => 0,
            '2019-02-07' => 0,
            '2019-02-08' => 0,
            '2019-02-09' => 0,
            '2019-02-10' => 0,
            '2019-02-11' => 0,
            '2019-02-12' => 0,
            '2019-02-13' => 0,
            '2019-02-14' => 0,
            '2019-02-15' => 0,
            '2019-02-16' => 0,
            '2019-02-17' => 0,
            '2019-02-18' => 0,
            '2019-02-19' => 0,
            '2019-02-20' => 0,
            '2019-02-21' => 0,
            '2019-02-22' => 0,
            '2019-02-23' => 0,
            '2019-02-24' => 0,
            '2019-02-25' => 0,
            '2019-02-26' => 0,
            '2019-02-27' => 0,
            '2019-02-28' => 0,
            '2019-03-01' => 0,
            '2019-03-02' => 0,
            '2019-03-03' => 0,
            '2019-03-04' => 0,
            '2019-03-05' => 0,
            '2019-03-06' => 0,
            '2019-03-07' => 0,
            '2019-03-08' => 0,
            '2019-03-09' => 0,
            '2019-03-10' => 0,
            '2019-03-11' => 0,
            '2019-03-12' => 0,
            '2019-03-13' => 0,
            '2019-03-14' => 0,
            '2019-03-15' => 0,
            '2019-03-16' => 0,
            '2019-03-17' => 0,
            '2019-03-18' => 0,
            '2019-03-19' => 0,
            '2019-03-20' => 0,
            '2019-03-21' => 0,
            '2019-03-22' => 0,
            '2019-03-23' => 0,
            '2019-03-24' => 0,
            '2019-03-25' => 0,
            '2019-03-26' => 0,
            '2019-03-27' => 0,
            '2019-03-28' => 0,
            '2019-03-29' => 0,
            '2019-03-30' => 0,
            '2019-03-31' => 0,
            '2019-04-01' => 0,
            '2019-04-02' => 0,
            '2019-04-03' => 0,
            '2019-04-04' => 0,
            '2019-04-05' => 0,
            '2019-04-06' => 0,
            '2019-04-07' => 0,
            '2019-04-08' => 0,
            '2019-04-09' => 0,
            '2019-04-10' => 0,
            '2019-04-11' => 0,
            '2019-04-12' => 0,
            '2019-04-13' => 0,
            '2019-04-14' => 0,
            '2019-04-15' => 0,
            '2019-04-16' => 0,
            '2019-04-17' => 0,
            '2019-04-18' => 0,
            '2019-04-19' => 0,
            '2019-04-20' => 0,
            '2019-04-21' => 0,
            '2019-04-22' => 0,
            '2019-04-23' => 0,
            '2019-04-24' => 0,
            '2019-04-25' => 0,
            '2019-04-26' => 0,
            '2019-04-27' => 0,
            '2019-04-28' => 0,
            '2019-04-29' => 0,
            '2019-04-30' => 0,
            '2019-05-01' => 0,
            '2019-05-02' => 0,
            '2019-05-03' => 0,
            '2019-05-04' => 0,
            '2019-05-05' => 0,
            '2019-05-06' => 0,
            '2019-05-07' => 0,
            '2019-05-08' => 0,
            '2019-05-09' => 0,
            '2019-05-10' => 0,
            '2019-05-11' => 0,
            '2019-05-12' => 0,
            '2019-05-13' => 0,
            '2019-05-14' => 0,
            '2019-05-15' => 0,
            '2019-05-16' => 0,
            '2019-05-17' => 0,
            '2019-05-18' => 0,
            '2019-05-19' => 0,
            '2019-05-20' => 0,
            '2019-05-21' => 0,
            '2019-05-22' => 0,
            '2019-05-23' => 0,
            '2019-05-24' => 0,
            '2019-05-25' => 0,
            '2019-05-26' => 0,
            '2019-05-27' => 0,
            '2019-05-28' => 0,
            '2019-05-29' => 0,
            '2019-05-30' => 0,
            '2019-05-31' => 0,
            '2019-06-01' => 0,
            '2019-06-02' => 0,
            '2019-06-03' => 0,
            '2019-06-04' => 0,
            '2019-06-05' => 0,
            '2019-06-06' => 0,
            '2019-06-07' => 0,
            '2019-06-08' => 0,
            '2019-06-09' => 0,
            '2019-06-10' => 0,
            '2019-06-11' => 0,
            '2019-06-12' => 0,
            '2019-06-13' => 0,
            '2019-06-14' => 0,
            '2019-06-15' => 0,
            '2019-06-16' => 0,
            '2019-06-17' => 0,
            '2019-06-18' => 0,
            '2019-06-19' => 0,
            '2019-06-20' => 0,
            '2019-06-21' => 0,
            '2019-06-22' => 0,
            '2019-06-23' => 0,
            '2019-06-24' => 0,
            '2019-06-25' => 0,
            '2019-06-26' => 0,
            '2019-06-27' => 0,
            '2019-06-28' => 0,
            '2019-06-29' => 0,
            '2019-06-30' => 0,
            '2019-07-01' => 0,
            '2019-07-02' => 0,
            '2019-07-03' => 0,
            '2019-07-04' => 0,
            '2019-07-05' => 0,
            '2019-07-06' => 0,
            '2019-07-07' => 0,
            '2019-07-08' => 0,
            '2019-07-09' => 0,
            '2019-07-10' => 0,
            '2019-07-11' => 0,
            '2019-07-12' => 0,
            '2019-07-13' => 0,
            '2019-07-14' => 0,
            '2019-07-15' => 0,
            '2019-07-16' => 0,
            '2019-07-17' => 0,
            '2019-07-18' => 0,
            '2019-07-19' => 0,
            '2019-07-20' => 0,
            '2019-07-21' => 0,
            '2019-07-22' => 0,
            '2019-07-23' => 0,
            '2019-07-24' => 0,
            '2019-07-25' => 0,
            '2019-07-26' => 0,
            '2019-07-27' => 0,
            '2019-07-28' => 0,
            '2019-07-29' => 0,
            '2019-07-30' => 0,
            '2019-07-31' => 0,
            '2019-08-01' => 0,
            '2019-08-02' => 0,
            '2019-08-03' => 0,
            '2019-08-04' => 0,
            '2019-08-05' => 0,
            '2019-08-06' => 0,
            '2019-08-07' => 0,
            '2019-08-08' => 0,
            '2019-08-09' => 0,
            '2019-08-10' => 0,
            '2019-08-11' => 0,
            '2019-08-12' => 0,
            '2019-08-13' => 0,
            '2019-08-14' => 0,
            '2019-08-15' => 0,
            '2019-08-16' => 0,
            '2019-08-17' => 0,
            '2019-08-18' => 0,
            '2019-08-19' => 0,
            '2019-08-20' => 0,
            '2019-08-21' => 0,
            '2019-08-22' => 0,
            '2019-08-23' => 0,
            '2019-08-24' => 0,
            '2019-08-25' => 0,
            '2019-08-26' => 0,
            '2019-08-27' => 0,
            '2019-08-28' => 0,
            '2019-08-29' => 0,
            '2019-08-30' => 0,
            '2019-08-31' => 0,
            '2019-09-01' => 0,
            '2019-09-02' => 0,
            '2019-09-03' => 0,
            '2019-09-04' => 0,
            '2019-09-05' => 0,
            '2019-09-06' => 0,
            '2019-09-07' => 0,
            '2019-09-08' => 0,
            '2019-09-09' => 0,
            '2019-09-10' => 0,
            '2019-09-11' => 0,
            '2019-09-12' => 0,
            '2019-09-13' => 0,
            '2019-09-14' => 0,
            '2019-09-15' => 0,
            '2019-09-16' => 0,
            '2019-09-17' => 0,
            '2019-09-18' => 0,
            '2019-09-19' => 0,
            '2019-09-20' => 0,
            '2019-09-21' => 0,
            '2019-09-22' => 0,
            '2019-09-23' => 0,
            '2019-09-24' => 0,
            '2019-09-25' => 0,
            '2019-09-26' => 0,
            '2019-09-27' => 0,
            '2019-09-28' => 0,
            '2019-09-29' => 0,
            '2019-09-30' => 0,
            '2019-10-01' => 0,
            '2019-10-02' => 0,
            '2019-10-03' => 0,
            '2019-10-04' => 0,
            '2019-10-05' => 0,
            '2019-10-06' => 0,
            '2019-10-07' => 0,
            '2019-10-08' => 0,
            '2019-10-09' => 0,
            '2019-10-10' => 0,
            '2019-10-11' => 0,
            '2019-10-12' => 0,
            '2019-10-13' => 0,
            '2019-10-14' => 0,
            '2019-10-15' => 0,
            '2019-10-16' => 0,
            '2019-10-17' => 0,
            '2019-10-18' => 0,
            '2019-10-19' => 0,
            '2019-10-20' => 0,
            '2019-10-21' => 0,
            '2019-10-22' => 0,
            '2019-10-23' => 0,
            '2019-10-24' => 0,
            '2019-10-25' => 0,
            '2019-10-26' => 0,
            '2019-10-27' => 0,
            '2019-10-28' => 0,
            '2019-10-29' => 0,
            '2019-10-30' => 0,
            '2019-10-31' => 0,
            '2019-11-01' => 0,
            '2019-11-02' => 0,
            '2019-11-03' => 0,
            '2019-11-04' => 0,
            '2019-11-05' => 0,
            '2019-11-06' => 0,
            '2019-11-07' => 100,
            '2019-11-08' => 100,
            '2019-11-09' => 0,
            '2019-11-10' => 0,
            '2019-11-11' => 0,
            '2019-11-12' => 0,
            '2019-11-13' => 0,
            '2019-11-14' => 0,
            '2019-11-15' => 0,
            '2019-11-16' => 0,
            '2019-11-17' => 0,
            '2019-11-18' => 0,
            '2019-11-19' => 0,
            '2019-11-20' => 0,
            '2019-11-21' => 0,
            '2019-11-22' => 0,
            '2019-11-23' => 0,
            '2019-11-24' => 0,
            '2019-11-25' => 0,
            '2019-11-26' => 0,
            '2019-11-27' => 0,
            '2019-11-28' => 0,
            '2019-11-29' => 0,
            '2019-11-30' => 0,
            '2019-12-01' => 0,
            '2019-12-02' => 0,
            '2019-12-03' => 0,
            '2019-12-04' => 0,
            '2019-12-05' => 0,
            '2019-12-06' => 0,
            '2019-12-07' => 100,
        ];

        $this->assertEquals($expected, $this->getCalculator()->getSumsByDay('Y-m-d')->getStats());
    }

    public function testSumByWeekReturnsExpectedCount(): void
    {
        $expected = [
            '2018-49' => 600,
            '2018-50' => 0,
            '2018-51' => 0,
            '2018-52' => 0,
            '2018-01' => 0,
            '2019-02' => 0,
            '2019-03' => 0,
            '2019-04' => 0,
            '2019-05' => 0,
            '2019-06' => 0,
            '2019-07' => 0,
            '2019-08' => 0,
            '2019-09' => 0,
            '2019-10' => 0,
            '2019-11' => 0,
            '2019-12' => 0,
            '2019-13' => 0,
            '2019-14' => 0,
            '2019-15' => 0,
            '2019-16' => 0,
            '2019-17' => 0,
            '2019-18' => 0,
            '2019-19' => 0,
            '2019-20' => 0,
            '2019-21' => 0,
            '2019-22' => 0,
            '2019-23' => 0,
            '2019-24' => 0,
            '2019-25' => 0,
            '2019-26' => 0,
            '2019-27' => 0,
            '2019-28' => 0,
            '2019-29' => 0,
            '2019-30' => 0,
            '2019-31' => 0,
            '2019-32' => 0,
            '2019-33' => 0,
            '2019-34' => 0,
            '2019-35' => 0,
            '2019-36' => 0,
            '2019-37' => 0,
            '2019-38' => 0,
            '2019-39' => 0,
            '2019-40' => 0,
            '2019-41' => 0,
            '2019-42' => 0,
            '2019-43' => 0,
            '2019-44' => 0,
            '2019-45' => 200,
            '2019-46' => 0,
            '2019-47' => 0,
            '2019-48' => 0,
            '2019-49' => 100,
        ];
        $this->assertEquals($expected, $this->getCalculator()->getSumsByWeek('Y-W')->getStats());
    }

    public function testSumByHoursReturnsExpectedCount(): void
    {
        $stats = new StatsDAO();
        $stats->getYear(2021)
            ->getMonth(12)
            ->getDay(7)
            ->getHour(1)
            ->setCount(100);

        $stats->getYear(2021)
            ->getMonth(12)
            ->getDay(7)
            ->getHour(3)
            ->setCount(200);

        $stats->getYear(2021)
            ->getMonth(12)
            ->getDay(7)
            ->getHour(12)
            ->setCount(50);

        $expected = [
            '2021-12-07 01' => 100,
            '2021-12-07 02' => 0,
            '2021-12-07 03' => 200,
            '2021-12-07 04' => 0,
            '2021-12-07 05' => 0,
            '2021-12-07 06' => 0,
            '2021-12-07 07' => 0,
            '2021-12-07 08' => 0,
            '2021-12-07 09' => 0,
            '2021-12-07 10' => 0,
            '2021-12-07 11' => 0,
            '2021-12-07 12' => 50,
        ];

        $dateFrom = new \DateTime('2021-12-07');
        $dateTo   = new \DateTime('2021-12-07');

        $calculatorObj = new Calculator($stats, $dateFrom, $dateTo);
        $this->assertEquals($expected, $calculatorObj->getCountsByHour()->getStats());
    }

    /**
     * @throws \Exception
     */
    private function getCalculator(): Calculator
    {
        $stats = new StatsDAO();

        $stats->getYear(2018)
            ->getMonth(12)
            ->getDay(7)
            ->getHour(12)
            ->setCount(100);

        $stats->getYear(2018)
            ->getMonth(12)
            ->getDay(7)
            ->getHour(13)
            ->setCount(200);

        $stats->getYear(2018)
            ->getMonth(12)
            ->getDay(8)
            ->getHour(14)
            ->setCount(300);

        $stats->getYear(2019)
            ->getMonth(11)
            ->getDay(7)
            ->getHour(12)
            ->setCount(100);

        $stats->getYear(2019)
            ->getMonth(11)
            ->getDay(8)
            ->getHour(12)
            ->setCount(100);

        $stats->getYear(2019)
            ->getMonth(12)
            ->getDay(7)
            ->getHour(12)
            ->setCount(100);

        $dateFrom = new \DateTime('2018-12-07');
        $dateTo   = new \DateTime('2019-12-07');

        return new Calculator($stats, $dateFrom, $dateTo);
    }
}
