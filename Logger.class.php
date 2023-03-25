<?php

namespace Birdey;

class Logger
{

    public $_logsArray = array();
    public static $_instance;
    public $_shouldLog;
    public $_shouldAutoShowLog = true;
    public $_shouldLogToFile = false;

    /*
     * Log Level
     * -1 = All
     * 0 = Off
     * 1 = Verbose and higher
     * 2 = Info  and higher
     * 3 = Warning and higher
     * 4 = Error and higher
     * 5 = Critical
     */

    public static $_logLevel = -1;

    public function __construct()
    {
        $this->_shouldLog = DEBUG;
    }

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Logger();
        }
        return self::$_instance;
    }

    private function shouldLog($level)
    {
        if ($this->_shouldLog && self::$_logLevel != 0 && self::$_logLevel <= $level) {
            return true;
        } else {
            return false;
        }
    }

    private function Log($message, $level, $shouldShowStacktrace = false)
    {
        if (count($this->_logsArray) > 1000) {
            array_shift($this->_logsArray);
        }

        if ($shouldShowStacktrace) {
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

        if ($this->_shouldLogToFile && $level >= 3) {
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

    private function logMessageToFile($message)
    {

        $wrongs  = array('<br>', '</br>', '<br/>', '</ br>', '<br />');
        $message = str_replace($wrongs, PHP_EOL, $message);

        $time    = \DateTime::createFromFormat('U.u', microtime(true));
        $date    = $time->format("Y-m-d H:i:s.u");
        $message = $date . " " . $message . PHP_EOL;

        $logFilePath = 'logs/' . $time->format("Y_m_d") . '.log';
        try {
            if (!file_exists($logFilePath)) {
                echo 'Error opening ' . $logFilePath;
                fopen($logFilePath, 'w+');
            }
            if ($logFile = fopen($logFilePath, 'a+')) {
                fwrite($logFile, $message);
                fclose($logFile);
            }
        } catch (\Exception $e) {
            echo 'Crap';
        }

    }

    private function getLogColor($level)
    {
        switch ($level) {
            case 1:
                return 'rgba(0,255,255,0.5)';
            case 2:
                return '#00FF00';
            case 3:
                return '#FFFF00';
            case 4:
                return '#FF5500';
            case 5:
                return '#FF0000';
            default:
                return '#00FF00';
        }
    }

    public function getLogs()
    {
        return $this->_logsArray;
    }

    private function isLocalhost($whitelist = ['127.0.0.1', '::1'])
    {
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }

    public function drawLogsBox()
    {
        if ($this->_shouldLog && count($this->_logsArray) > 0 && DEBUG) {
            ?>
            <style>
                #logbox {
                    position: fixed;
                    bottom: 10px;
                    right: 10px;
                    max-height: 90vh;
                    min-width: 20vw;
                    max-width: 100vw;
                    overflow: scroll;
                }

                #logbox>table {
                    background: rgba(40, 60, 50, 1);
                    border-left: 5px solid rgba(0, 0, 0, 0.5);
                    border-top: 5px solid rgba(0, 0, 0, 0.5);
                    border-right: 5px solid rgba(255, 255, 255, 0.5);
                    border-bottom: 5px solid rgba(255, 255, 255, 0.5);
                    width: 100%;
                }

                #logbox>.logheader {
                    font-size: 1.4rem;
                }

                .logclass_1 {
                    color: rgba(0, 255, 255, 0.5);
                    font-size: 0.8rem;
                    line-height: 0.8rem;
                }

                .logclass_2 {
                    color: #00FF00;
                    font-size: 1rem;
                    line-height: 1rem;
                }

                .logclass_3 {
                    color: #FFFF00;
                    font-size: 1.2rem;
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
            $this->_logsArray = array_reverse($this->_logsArray);
            echo '<div class="logbox" id="logbox">';
            echo '<table>';
            echo '<tr class="logHeader" >';
            echo '<th style="color: white;">Logs <span>+</span></th>';
            echo '</tr>';
            foreach ($this->_logsArray as $log) {
                $level    = $log['l'];
                $color    = $this->getLogColor($level);
                $logClass = 'logclass_' . $level;

                $size = (4 + ($level * 4)) . "px";
                echo "<tr class='$logClass'>";
                echo '<td class="logData"><code>', $this->splitString($log['m']), '</code></td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }
    }

    public function splitString($str)
    {
        $array = explode("\n", wordwrap($str, 160));
        return implode("<br/>", $array);
    }

    /* LOG FUNCTIONS */

    public static function Verbose($message, $st = false)
    {
        Logger::getInstance()->logVerbose($message, $st);
    }

    public static function Info($message, $st = false)
    {
        Logger::getInstance()->logInfo($message, $st);
    }

    public static function Warning($message, $st = false)
    {
        Logger::getInstance()->_shouldAutoShowLog = true;
        Logger::getInstance()->logWarning($message, $st);
    }

    public static function Error($message, $st = false)
    {
        Logger::getInstance()->_shouldAutoShowLog = true;
        Logger::getInstance()->logError($message, $st);
    }

    public static function Critical($message, $st = false)
    {
        Logger::getInstance()->_shouldAutoShowLog = true;
        Logger::getInstance()->logCritical($message, $st);
    }

    private function LogVerbose($message, $st = false)
    {
        $this->Log($message, 1, $st);
    }

    private function LogInfo($message, $st = false)
    {
        $this->Log($message, 2, $st);
    }

    private function LogWarning($message, $st = false)
    {
        $this->Log($message, 3, $st);
    }

    private function LogError($message, $st = false)
    {
        $this->Log($message, 4, $st);
    }

    private function LogCritical($message, $st = false)
    {
        $this->Log($message, 5, $st);
    }

}