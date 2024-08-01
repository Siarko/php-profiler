<?php

namespace Siarko\Profiler;

class Profiler
{

    private static int $level = 0;
    private static array $ids = [];
    private static bool $enabled = true;

    private static ?TimeFactor $timeFactor = null;

    private const LOG = 0;
    private const TF = 1;

    /**
     * @var TimeLog[]
     */
    private static array $logs = [];

    /**
     * @param $id
     * @param TimeFactor|null $timeFactor
     * @return void
     */
    public static function start($id = null, ?TimeFactor $timeFactor = null): void
    {
        if (!self::isEnabled()) {
            return;
        }

        if (is_null($timeFactor)) {
            $timeFactor = self::getTimeFactor();
        }
        $cc = self::getCaller();
        $name = $cc['name'];
        if(!is_null($id)){
            $name .= " [{$id}]";
        }
        $key = $name . "_" . self::$level;
        if (array_key_exists($key, self::$logs)) {
            self::$logs[$key][self::LOG]->new();
        } else {
            $log = new TimeLog();
            self::$logs[$key] = [
                self::LOG => $log,
                self::TF => $timeFactor
            ];
        }

        self::$ids[] = $key;
        self::$level++;
    }

    /**
     * @return void
     */
    public static function end(): void
    {
        if (!self::isEnabled()) {
            return;
        }
        self::$level--;
        $id = array_pop(self::$ids);
        if(!is_null($id)){
            $log = self::$logs[$id][self::LOG];
            $log->end();
        }
    }

    /**
     * @return TimeLog[]
     */
    public static function getLogs(): array
    {
        return self::$logs;
    }

    /**
     * @return void
     */
    public static function print(): void
    {
        if(self::isEnabled()){
            if (!empty(self::$logs)) {
                echo "Profiling results:\n";
                echo self::getProfilerSummary();
            }else{
                echo "Profiling: No logs were collected\n";
            }
        }
    }

    /**
     * @return string
     */
    public static function getProfilerSummary(): string
    {
        $result = "";

        $maxLen = 0;
        foreach (self::$logs as $k => $log) {
            $indentCount = (int)substr($k, strrpos($k, '_') + 1);
            $len = $indentCount + strlen($k);
            if ($len > $maxLen) {
                $maxLen = $len;
            }
        }

        $maxLen += 2;

        foreach (self::$logs as $key => $log) {
            /** @var TimeLog $timeLog */
            $timeLog = $log[self::LOG];
            $tf = $log[self::TF];
            $indentCount = (int)substr($key, strrpos($key, '_') + 1);
            $id = substr($key, 0, strrpos($key, '_'));
            $indent = str_repeat(' ', $indentCount);

            $min = $timeLog->getMinTime($tf);
            $max = $timeLog->getMaxTime($tf);
            $avg = $timeLog->getAvgTime($tf);
            $sum = $timeLog->getSumTime($tf);
            $tfName = $tf->name;

            $separatorLen = $maxLen - ($indentCount + strlen($key));
            $separator = str_repeat(' ', $separatorLen);


            $result .= $indent . $id . "{$separator}({$timeLog->getSampleCount()}) MIN:{$min} AVG:{$avg} MAX:{$max} SUM:{$sum} [{$tfName}]\n";
        }

        return $result;
    }

    /**
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    /**
     * @param bool $enabled
     */
    public static function setEnabled(bool $enabled): void
    {
        self::$enabled = $enabled;
    }

    /**
     * @return TimeFactor
     */
    public static function getTimeFactor(): TimeFactor
    {
        if (is_null(self::$timeFactor)) {
            self::setDefaultTimeFactor(TimeFactor::SECONDS);
        }
        return self::$timeFactor;
    }

    /**
     * @param TimeFactor $timeFactor
     */
    public static function setDefaultTimeFactor(TimeFactor $timeFactor): void
    {
        self::$timeFactor = $timeFactor;
    }

    /**
     * @return array
     */
    private static function getCaller(): array
    {

        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $caller = null;
        if(count($trace) >= 3){
            $caller = $trace[2];
        }

        // +1 to i cos we have to account for calling this function
        if (!is_null($caller) && array_key_exists('class', $caller)) {
            $class = $caller['class'];
            if(array_key_exists('object', $caller)){
                $class = get_class($caller['object']);
            }
            $classShortName = substr($class, strrpos($class, '\\') + 1);
            return [
                'name' => $classShortName . '::' . $caller['function'] . '()',
                'data' => $caller
            ];
        }else{
            return [
                'name' => 'init',
                'data' => []
            ];
        }
    }
}