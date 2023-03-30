<?php

namespace Birdey;

enum LogLevel: int
{
    case All = -1;
    case Off = 0;
    case Verbose = 1;
    case Info = 2;
    case Warning = 3;
    case Error = 4;
    case Critical = 5;

}

?>