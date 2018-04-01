<?php
namespace braulioRam\dealsNotifier\Base;

use GetOpt\GetOpt;

Class ParameterFetcher {
    protected static $getOpt;


    protected static function getOpt()
    {
        if (!is_object(self::$getOpt)) {
            $settings = [
                GetOpt::SETTING_STRICT_OPTIONS => false,
                GetOpt::SETTING_STRICT_OPERANDS => false,
            ];

            self::$getOpt = new GetOpt(null, $settings);
            self::$getOpt->process();
        }

        return self::$getOpt;
    }


    public static function get($name)
    {
        return self::getOpt()->getOption($name);
    }


    public static function all()
    {
        return self::getOpt()->getOptions();
    }
}
