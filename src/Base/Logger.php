<?php
namespace braulioRam\dealsNotifier\Base;

use League\CLImate\CLImate;

Class Logger {
    protected static $climate;
    protected static $verbose;


    public static function log($message, $level)
    {
        $color = static::getColor($level);

        if ($color == 'blue' && !self::$verbose) {
            return;
        }

        return self::climate()->$color($message);
    }


    public static function verbose($verbose = false)
    {
        self::$verbose = $verbose;
    }


    public static function climate()
    {
        if (!is_object(self::$climate)) {
            self::$climate = new CLImate;
        }

        return self::$climate;
    }


    protected static function getColor($level)
    {
        switch ($level) {
            case 'error':
                return 'red';
                break;
            case 'warning':
                return 'yellow';
                break;
            case 'notice':
                return 'green';
                break;
            case 'debug':
                return 'blue';
                break;
            default:
                return 'log';
                break;
        }
    }
}
