<?php

namespace App\Event;

use App\Entity\ChartReport;
use Symfony\Contracts\EventDispatcher\Event;

class ChartReportedEvent extends Event
{
    public const NAME = 'chart.reported';

    public function __construct(
        private readonly ChartReport $chartReport
    ) {}

    public function getChartReport(): ChartReport
    {
        return $this->chartReport;
    }
}
