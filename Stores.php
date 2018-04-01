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
            'amazonmx' => [
                'parser' => 'braulioRam\dealsNotifier\Amazon\AmazonParser',
                'domain' => 'https://www.amazon.com.mx',
                'listings' => [
                    'guitar-amplifiers' => '/b?ie=UTF8&node=14652402011'
                ]
            ]
        ];
    }
}
