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

    public function testGetOverallPercentage()
    {
        self::assertEquals($this->getOverallPercentage(), $this->dataset->getOverallPercentage());
    }

    public function testGetOverallPercentageVarianz()
    {
        self::assertEquals($this->getOverallPercentageVarianz(), $this->dataset->getOverallPercentageVarianz());
    }

    public function testGetOverallPercentageStandardabweichung()
    {
        self::assertEquals(sqrt($this->getOverallPercentageVarianz()), $this->dataset->getOverallPercentageStandardabweichung());
    }

    public function testGetPercentageStandardAbweichungPerBlock()
    {
        self::assertEquals($this->getSdPerBlock(), $this->dataset->getPercentageStandardabweichungPerBlock());
    }
}
