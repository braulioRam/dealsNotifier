<?php
namespace braulioRam\dealsNotifier;

Class Stores {
    public static function getStores()
    {
        return [
            'amazon' => [
                'parser' => 'braulioRam\dealsNotifier\Amazon\AmazonParser',
                'domain' => 'https://www.amazon.com',
                'listings' => [
                    'guitar-amplifiers' => '/b/ref=lp_11091801_ln_3_0?node=486410011&ie=UTF8&qid=1522449197'
                ]
            ],
            'amazon-mx' => [
                'parser' => 'braulioRam\dealsNotifier\Amazon\AmazonParser',
                'domain' => 'https://www.amazon.com.mx',
                'listings' => [
                    'electric-guitars' => '/b?ie=UTF8&node=14652407011',
                    'guitar-amplifiers' => '/b?ie=UTF8&node=14652402011',
                    'guitar-effects' => '/b?ie=UTF8&node=14652403011',
                    'cool-headphones' => '/Aud%C3%ADfonos-Marca-6-seleccionados/s?ie=UTF8&page=1&rh=n%3A9687308011%2Cp_89%3AAKG%7CAudio-Technica%7CBose%7CSennheiser%7CSony%7Cbeyerdynamic'
                ]
            ]
        ];
    }
}
