<?php

namespace Siarko\Profiler;

class TimeLog
{

    private array $logs = [];
    private int $startTime;
    private int $endTime;

    /**
     * TimeLog constructor.
     */
    public function __construct()
    {
        $this->new();
    }

    /**
     * @return void
     */
    public function new(): void
    {
        $this->startTime = hrtime(true);
    }

    /**
     * @return void
     */
    public function end(): void
    {
        $this->endTime = hrtime(true);
        $this->logs[] = $this->endTime-$this->startTime;
    }

    /**
     * @return int
     */
    public function getSampleCount(): int
    {
        return count($this->logs);
    }

    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float
     */
    public function getMinTime(TimeFactor $timeFactor, int $precision = 3): float
    {
        if(empty($this->logs)){ return 0.0; }
        $min = $this->logs[0];
        foreach ($this->logs as $log) {
            if($log < $min){
                $min = $log;
            }
        }
        return round($min/$timeFactor->value, $precision);
    }

    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float
     */
    public function getMaxTime(TimeFactor $timeFactor, int $precision = 3): float
    {
        if(empty($this->logs)){ return 0.0; }
        $min = $this->logs[0];
        foreach ($this->logs as $log) {
            if($log > $min){
                $min = $log;
            }
        }
        return round($min/$timeFactor->value, $precision);
    }


    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float
     */
    public function getAvgTime(TimeFactor $timeFactor, int $precision = 3): float
    {
        $divisions = count($this->logs);
        if($divisions == 0){
            return 0.0;
        }
        $avg = array_sum($this->logs)/$divisions;
        return round($avg/$timeFactor->value, $precision);
    }

    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float
     */
    public function getSumTime(TimeFactor $timeFactor, int $precision = 3): float
    {
        return round(array_sum($this->logs)/$timeFactor->value, $precision);
    }

}