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

class Logger
{
    public static LogLevel $_logLevel = LogLevel::Verbose;
    public $_logsArray = array();
    public bool $_shouldLogToScreen = true;
    public bool $_shouldLogToFile = true;



    public static $_instance;

    public static function getInstance(): Logger
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Logger();
        }
        return self::$_instance;
    }

    private function shouldLog(LogLevel $level): bool
    {
        if (
            $this->_shouldLogToScreen == false || self::$_logLevel == LogLevel::Off || self::$_logLevel->value > $level->value) {
            return false;
        }
        return true;
    }

    private function Log(string $message, LogLevel $level, bool $logStackTrace = false): void
    {
        if (count($this->_logsArray) > 1000) {
            array_shift($this->_logsArray);
        }

        if ($logStackTrace || $level->value == LogLevel::Critical->value) {
            $bt = debug_backtrace();
            array_shift($bt);
            array_shift($bt);
            $btCount = count($bt);
            foreach ($bt as $key => $val) {
                $file    = $val['file'] ?? 'null';
                $line    = $val['line'] ?? 'null';
                $message .= "<br/>st($btCount): $file($line)";
                $btCount--;
            }
        }

        if ($this->_shouldLogToFile && $level->value >= LogLevel::Warning->value) {
            $this->logMessageToFile($message);
        }

        if ($this->shouldLog($level)) {
            $msg = array(
                'm' => $message,
                'l' => $level,
                't' => date('G:i:s'),
            );
            array_push($this->_logsArray, $msg);
        }
    }

    private function logMessageToFile(string $message): void
    {

        $wrongs  = array('<br>', '</br>', '<br/>', '</ br>', '<br />');
        $message = str_replace($wrongs, PHP_EOL, $message);

        $time    = \DateTime::createFromFormat('U.u', microtime(true));
        $date    = $time->format("Y-m-d H:i:s.u");
        $message = $date . " " . $message . PHP_EOL;

        $logFilePath = 'logs/' . $time->format("Y_m_d") . '.log';
        try {
            if (!file_exists($logFilePath)) {
                fopen($logFilePath, 'w+');
            }
            if ($logFile = fopen($logFilePath, 'a+')) {
                fwrite($logFile, $message);
                fclose($logFile);
            }
        } catch (\Exception $e) {
        }

    }

    public function getLogs(): array
    {
        return $this->_logsArray;
    }

    public static function DrawLogBox(): void
    {
        Logger::getInstance()->drawLogsBox();
    }

    private function drawLogsBox(): bool
    {
        if ($this->_shouldLogToScreen == false || empty($this->_logsArray)) {
            return false;
        }

        ?>
        <style>
            #logbox {
                position: fixed;
                overflow: scroll;
                top: 5px;
                right: 5px;
                max-height: calc(100vh - 10px);
                max-width: calc(100vw - 10px);
                z-index: 100;
                box-shadow: 1px 2px 3px black;
                border: 5px solid green;
                background: black;
                font-family: monospace;
                font-size: 0.8rem;
                line-height: 1rem;
            }

            #logbox>table {
                Background: #000;
                width: 100%;
            }

            #logbox tr:nth-child(2n) {
                Background: rgb(0, 40, 0);
            }

            .logRow {
                display: none;
            }

            .logHeader {
                font-size: 1.2rem;
                line-height: 2rem;
            }

            .logclass_1 {
                color: rgba(0, 255, 255, 0.5);
                width: 100%;
            }

            .logclass_2 {
                color: #00FF00;
                width: 100%;
            }

            .logclass_3 {
                color: #FFFF00;
                width: 100%;
            }

            .logclass_4 {
                color: #FF0000;
                width: 100%;
            }

            .logclass_5 {
                display: inline-block;
                border: thin solid #FF0000;
                padding: 0.2rem;
                color: #FF5500;
                width: 100%;
            }
        </style>
        <?php
        //$this->_logsArray = array_reverse($this->_logsArray);
        echo '<div class="logbox" id="logbox">';
        echo '<table>';
        echo '<th class="logHeader" id="logHeader" onclick="toggleLog()" style="color: white; ">- LOGGER CONSOLE -</th>';
        foreach ($this->_logsArray as $log) {
            $level    = $log['l'];
            $logClass = 'logclass_' . $level->value;

            echo "<tr class='logRow $logClass'>";
            echo '<td class="logData">', nl2br($log['m']), '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        ?>
        <script>
            function toggleLog() {
                console.log('Toggling LogBox');
                Array.from(document.getElementsByClassName('logRow')).forEach(
                    (element, index, array) => {
                        if (element.style.display == 'block') {
                            element.style.display = 'none';
                        } else {
                            element.style.display = 'block';
                        }
                    }
                );
            }
        </script>
        <?php
        Logger::Critical('DRAWING MULTIPLE LOGBOXES!', true);
        return true;
    }

    public function splitString(string $str): string
    {
        $array = explode("\n", wordwrap($str, 160));
        return implode("<br/>", $array);
    }

    public static function setShouldLog(bool $shouldLog): void
    {
        LOGGER::getInstance()->_shouldLogToScreen = $shouldLog;
    }

    /* LOG FUNCTIONS */

    public static function Verbose(string $message, bool $logStackTrace = false): void
    {
        Logger::getInstance()->Log($message, LogLevel::Verbose, $logStackTrace);
    }

    public static function Info(string $message, bool $logStackTrace = false): void
    {
        Logger::getInstance()->Log($message, LogLevel::Info, $logStackTrace);
    }

    public static function Warning(string $message, bool $logStackTrace = false): void
    {
        Logger::getInstance()->Log($message, LogLevel::Warning, $logStackTrace);
    }

    public static function Error(string $message, bool $logStackTrace = false): void
    {
        Logger::getInstance()->Log($message, LogLevel::Error, $logStackTrace);
    }

    public static function Critical(string $message, bool $logStackTrace = false): void
    {
        Logger::getInstance()->Log($message, LogLevel::Critical, $logStackTrace);
    }

    public static function TestLogTypes(): void
    {
        Logger::Info('----------------');
        Logger::Info('TEST OF LOGGER MESSAGE LEVELS');
        Logger::Verbose('Verbose Message Test');
        Logger::Verbose('Verbose Stacktrace Test', true);
        Logger::Info('Info Message Test');
        Logger::Info('Info Stacktrace Test', true);
        Logger::Warning('Warning Message Test');
        Logger::Warning('Warning Stacktrace Test', true);
        Logger::Error('Error Message Test');
        Logger::Error('Error Stacktrace Test', true);
        Logger::Critical('Critical Message Test');
        Logger::Critical('Critical Stacktrace Test', true);
        Logger::Info('----------------');
    }


}