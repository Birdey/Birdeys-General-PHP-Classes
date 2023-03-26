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
    public static LogLevel $_logLevel = LogLevel::Info;
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

        if ($logStackTrace) {
            $bt = debug_backtrace(0);
            array_shift($bt);
            array_shift($bt);
            $btCount = count($bt);
            foreach ($bt as $key => $val) {
                $file = $val['file'];
                // $file = str_replace('C:\xampp\htdocs\proj\conshop_se\\', "", $file);
                $line    = $val['line'];
                $message .= "<br/>st($btCount): [$file($line)]";
                //$message  .= '<br/>st('.$count--.'): [' . $file . ':' . $line . ']';
                $btCount--;
            }
        }

        if ($this->_shouldLogToFile && $level >= LogLevel::Warning) {
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
                bottom: 0px;
                right: 0px;
                max-height: 90vh;
                min-width: 20vw;
                max-width: 100vw;
                overflow: scroll;
                pointer-events: scroll;
            }

            #logbox>table {
                background: rgba(40, 60, 50, 0.8);
                border-left: 5px solid rgba(0, 0, 0, 0.5);
                border-top: 5px solid rgba(0, 0, 0, 0.5);
                border-right: 5px solid rgba(255, 255, 255, 0.5);
                border-bottom: 5px solid rgba(255, 255, 255, 0.5);
                width: 100%;
            }

            #logbox tr:nth-child(2n) {
                background: rgba(10, 0, 10, 0.2);
            }

            .logheader {
                font-size: 1rem;
            }

            .logclass_1 {
                color: rgba(0, 255, 255, 0.5);
                font-size: 0.5rem;
                line-height: 0.5rem;
            }

            .logclass_2 {
                color: #00FF00;
                font-size: 0.7rem;
                line-height: 0.7rem;
            }

            .logclass_3 {
                color: #FFFF00;
                font-size: 1.0rem;
            }

            .logclass_4 {
                color: #FF0000;
                font-size: 1.4rem;
            }

            .logclass_5 {
                display: inline-block;
                border: thick double #FF0000;
                padding: 0.2rem;
                color: #FF5500;
                font-size: 1.6rem;
            }
        </style>
        <?php
        //$this->_logsArray = array_reverse($this->_logsArray);
        echo '<div class="logbox" id="logbox">';
        echo '<table>';
        echo '<tr class="logHeader" >';
        echo '<th style="color: white;">Logs <span>+</span></th>';
        echo '</tr>';
        foreach ($this->_logsArray as $log) {
            $level    = $log['l'];
            $logClass = 'logclass_' . $level->value;

            echo "<tr class='$logClass'>";
            echo '<td class="logData"><code>', $this->splitString($log['m']), '</code></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';

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


}