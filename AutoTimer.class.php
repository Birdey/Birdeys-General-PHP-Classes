<?php

namespace Birdey;

class AutoTimer
{
    private $object;

    public function __construct($object)
    {
        $this->object = $object;
        Logger::Info("Wraping " . get_class($object) . " in " . get_class($this));
    }

    public function __call($method, $args)
    {
        if (method_exists($this->object, $method)) {
            $startTime        = microtime(true);
            $returnValue      = call_user_func_array([$this->object, $method], $args);
            $runTime          = microtime(true) - $startTime;
            $formattedRunTime = number_format($runTime, 4, ",", ".");
            $className        = get_class($this->object);
            if ($runTime >= 0.0001)
                Logger::Verbose("<div>$className called function $method and it took $formattedRunTime ms to execute!<br><div>");
            return $returnValue;
        }
    }


}

?>