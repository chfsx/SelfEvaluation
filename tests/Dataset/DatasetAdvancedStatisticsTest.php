<?php

declare(strict_types=1);

include_once "DatasetHelperTrait.php";

use PHPUnit\Framework\TestCase;
use ilub\plugin\SelfEvaluation\Dataset\Dataset;

class DatasetAdvancedStatisticsTest extends TestCase
{
    use DatasetHelperTrait;

    protected Dataset $dataset;
    protected ilDBInterface $db;

    protected function setUp(): void
    {
        $this->db = Mockery::mock("\ilDBInterface");
        $this->dataset = new Dataset($this->db);
        $this->dataset = $this->setUpDatasetWithThreeBlocks($this->dataset);
    }

    public function testGetOverallPercentage(): void
    {
        self::assertEquals($this->getOverallPercentage(), $this->dataset->getOverallPercentage());
    }

    public function testGetOverallPercentageVarianz(): void
    {
        self::assertEquals($this->getOverallPercentageVarianz(), $this->dataset->getOverallPercentageVarianz());
    }

    public function testGetOverallPercentageStandardabweichung(): void
    {
        self::assertEquals(
            sqrt($this->getOverallPercentageVarianz()),
            $this->dataset->getOverallPercentageStandardabweichung()
        );
    }

    public function testGetPercentageStandardAbweichungPerBlock(): void
    {
        self::assertEquals($this->getSdPerBlock(), $this->dataset->getPercentageStandardabweichungPerBlock());
    }
}
