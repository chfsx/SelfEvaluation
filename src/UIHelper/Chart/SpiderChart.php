<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper\Chart;

use ilChartSpider;

class SpiderChart extends ilChartSpider
{
    use ChartHelper;

    public function __construct(string $a_id)
    {
        parent::__construct($a_id);
        $this->setSize($this->getCanvasWidth(), $this->getCanvasHeight());
        $this->setColors($this->getChartColors());
        $this->setLegend($this->getLegend());
        $this->setAutoResize(true);
    }
}
