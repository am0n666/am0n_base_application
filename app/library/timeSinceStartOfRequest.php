<?php

function timeSinceStartOfRequest(){
    if (isset($_SERVER['REQUEST_TIME_FLOAT'])
       && \is_float($_SERVER['REQUEST_TIME_FLOAT'])
    ) {
        $end = \microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        return round($end*1000, 2);
    }
    
    return 0;
}
