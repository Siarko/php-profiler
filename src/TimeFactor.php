<?php

namespace Siarko\Profiler;

enum TimeFactor: int
{
    CASE NANOSECONDS = 1;
    CASE MICROSECONDS = 1000;
    CASE MILLISECONDS = 1000000;
    CASE SECONDS = 1000000000;
}