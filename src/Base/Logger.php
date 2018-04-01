<?php
namespace braulioRam\dealsNotifier\Base;

use League\CLImate\CLImate;

Class Logger {
    protected static $climate;


    public static function log($message, $level)
    {
        $color = static::getColor($level);
        return self::climate()->$color($message);
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
            case 'debug':
                return 'blue';
                break;
            case 'notice':
                return 'green';
                break;
            default:
                return 'log';
                break;
        }
    }
}
