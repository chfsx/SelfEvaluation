<?php



use PHPUnit\Framework\TestCase;
use ilub\plugin\SelfEvaluation\Dataset\Statistics;

class StatisticsTest extends TestCase
{
    /**
     * @var Statistics
     */
    protected $statistics;



    public function setUp(): void
    {
        $this->statistics = new ilub\plugin\SelfEvaluation\Dataset\Statistics();
    }

    public function testConstruct()
    {
        self::assertEquals(Statistics::class, get_class($this->statistics));
    }

    public function testGetMeanFromDataOnEmpty()
    {
        self::assertEquals(null, $this->statistics->getMeanFromData([]));
    }

    public function testGetMeanFromDataOnOneEntry()
    {
        self::assertEquals(0, $this->statistics->getMeanFromData([0]));
        self::assertEquals(1, $this->statistics->getMeanFromData([1]));
    }

    public function testGetMeanFromDataFromMultipleEntries()
    {
        self::assertEquals(0.5, $this->statistics->getMeanFromData([0,1]));
        self::assertEquals((1 + 2 + 30) / 3, $this->statistics->getMeanFromData([1,2,30]));
    }

    public function testValueToPercentage()
    {
        self::assertEquals(0, $this->statistics->valueToPercentage(0));
        self::assertEquals(100, $this->statistics->valueToPercentage(1));
        self::assertEquals(30, $this->statistics->valueToPercentage(0.3));
        self::assertEquals(200, $this->statistics->valueToPercentage(2));
        self::assertEquals(100, $this->statistics->valueToPercentage(-1));

    }
    public function testFractionOfZero()
    {
        self::assertEquals(0, $this->statistics->fractionOf(0, 0));
        $this->expectException(\Exception::class);
        self::assertNull($this->statistics->fractionOf(1, 0));
    }
    public function testFractionOf()
    {
        self::assertEquals(1, $this->statistics->fractionOf(1, 1));
        self::assertEquals(1 / 3, $this->statistics->fractionOf(1, 3));
    }
    public function testPercentageOf()
    {
        self::assertEquals(1 * 100, $this->statistics->percentageOf(1, 1));
        self::assertEquals(1 / 3 * 100, $this->statistics->percentageOf(1, 3));
    }

    public function testArraySumFractionOfMaxSumPossible()
    {
        self::assertEquals(0, $this->statistics->arraySumFractionOfMaxSumPossible([0], 1));
        self::assertEquals(1 / (1 * 1), $this->statistics->arraySumFractionOfMaxSumPossible([1], 1));
        self::assertEquals((1 + 2) / (2 * 2), $this->statistics->arraySumFractionOfMaxSumPossible([1,2], 2));
        self::assertEquals((1 + 2 + 30) / (3 * 100), $this->statistics->arraySumFractionOfMaxSumPossible([1,2,30], 100));
    }

    public function testGetMinKeyAndValueFromArray()
    {
        self::assertEquals([0,0], $this->statistics->getMinKeyAndValueFromArray([0]));
        self::assertEquals([0,1], $this->statistics->getMinKeyAndValueFromArray([1]));
        self::assertEquals([1,3], $this->statistics->getMinKeyAndValueFromArray([5,3,7]));
    }

    public function testGetMinKeyAndValueFromAssArray()
    {
        self::assertEquals(["id1",0], $this->statistics->getMinKeyAndValueFromArray(["id1" => 0]));
        self::assertEquals(["id1",1], $this->statistics->getMinKeyAndValueFromArray(["id1" => 1]));
        self::assertEquals(["id2",3], $this->statistics->getMinKeyAndValueFromArray(["id1" => 5,"id2" => 3,"id3" => 7]));
    }

    public function testGetMaxKeyAndValueFromArray()
    {
        self::assertEquals([0,0], $this->statistics->getMaxKeyAndValueFromArray([0]));
        self::assertEquals([0,1], $this->statistics->getMaxKeyAndValueFromArray([1]));
        self::assertEquals([2,7], $this->statistics->getMaxKeyAndValueFromArray([5,3,7]));
    }

    public function testGetMaxKeyAndValueFromAssArray()
    {
        self::assertEquals(["id1",0], $this->statistics->getMaxKeyAndValueFromArray(["id1" => 0]));
        self::assertEquals(["id1",1], $this->statistics->getMaxKeyAndValueFromArray(["id1" => 1]));
        self::assertEquals(["id3",7], $this->statistics->getMaxKeyAndValueFromArray(["id1" => 5,"id2" => 3,"id3" => 7]));
    }

    public function testGetVarianzFromValues()
    {
        self::assertEquals(0, $this->statistics->getVarianzFromValues([1]));
        self::assertEquals((pow((1 - 1.5), 2) + pow((2 - 1.5), 2)) / 2, $this->statistics->getVarianzFromValues([1,2]));
        self::assertEquals((pow(1 - 11, 2) + pow(2 - 11, 2) + pow(30 - 11, 2)) / 3, $this->statistics->getVarianzFromValues([1,2,30]));
    }

    public function testGetStandardDeviationFromValuesAndAverage()
    {
        self::assertEquals(sqrt(0), $this->statistics->getStandardDeviation([1]));
        self::assertEquals(
            sqrt((pow((1 - 1.5), 2) + pow((2 - 1.5), 2)) / 2),
            $this->statistics->getStandardDeviation([1, 2])
        );
        self::assertEquals(
            sqrt((pow(1 - 11, 2) + pow(2 - 11, 2) + pow(30 - 11, 2)) / 3),
            $this->statistics->getStandardDeviation([1, 2, 30])
        );
    }
}
