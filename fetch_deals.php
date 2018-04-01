<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('America/Mexico_City');
require __DIR__ . '/vendor/autoload.php';

use braulioRam\dealsNotifier\Base\DealsNotifier;
use braulioRam\dealsNotifier\Base\StoreIdentifier;
use braulioRam\dealsNotifier\Base\ParameterFetcher;

$lineParameters = ParameterFetcher::all();
$storeIdentifier = new StoreIdentifier($lineParameters);
$storeParser = $storeIdentifier->getStoreParser($lineParameters);
$deals = $storeParser->getDeals($lineParameters);
$dealsNotifier = new DealsNotifier($deals, $lineParameters);
$dealsNotifier->notifyDeals();