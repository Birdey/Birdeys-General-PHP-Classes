<?php

namespace Birdey;

class ErrorFormatter
{
    public static function formatException(\Throwable $e): string
    {
        $html = "<div style='background-color: #ffdddd; border: 1px solid #ff9999; padding: 1em;'>\n";
        $html .= "<h3 style='color: #ff0000;'>Exception:</h3>\n";
        $html .= "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>\n";
        $html .= "<p>In file: <code>" . htmlspecialchars($e->getFile()) . "</code> on line <code>" . htmlspecialchars($e->getLine()) . "</code></p>\n";
        $html .= "<h3>Stack Trace:</h3>\n";
        $html .= "<pre style='color: #888888; background-color: #ffffff; border: 1px solid #dddddd; padding: 1em; overflow: scroll;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
        $html .= "</div>\n";

        Logger::Warning($e->getMessage());
        Logger::Warning("In file: " . $e->getFile() . " on line " . $e->getLine());
        Logger::Warning($e->getTraceAsString());
        Logger::DrawLogBox();
        return $html;
    }

    public static function formatError(int $errno, string $errstr, string $errfile, int $errline): string
    {
        $html = "<div style='background-color: #ffdddd; border: 1px solid #ff9999; padding: 1em;'>\n";
        $html .= "<h3 style='color: #ff0000;'>Error:</h3>\n";
        $html .= "<p><strong>" . htmlspecialchars($errstr) . "</strong></p>\n";
        $html .= "<p>In file: <code>" . htmlspecialchars($errfile) . "</code> on line <code>" . htmlspecialchars($errline) . "</code></p>\n";
        $html .= "<p>Error number: <code>" . htmlspecialchars($errno) . "</code></p>\n";
        $html .= "</div>\n";

        Logger::Warning($errstr);
        Logger::Warning("In file: " . $errfile . " on line " . $errline);
        Logger::Warning("Error Number: " . $errno);
        Logger::DrawLogBox();
        return $html;
    }
}