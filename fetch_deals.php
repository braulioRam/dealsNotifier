<?php
require_once('init.php');

use braulioRam\dealsNotifier\Base\Logger;
use braulioRam\dealsNotifier\Base\EmailNotifier;
use braulioRam\dealsNotifier\Base\StoreIdentifier;
use braulioRam\dealsNotifier\Base\ParameterFetcher;

$lineParameters = ParameterFetcher::all();
$verbose = isset($lineParameters['verbose']);
Logger::verbose($verbose);
$storeIdentifier = new StoreIdentifier($lineParameters);
$storeParser = $storeIdentifier->getStoreParser($lineParameters);
$listingName = $storeParser->getListingName();
$storeName = $storeParser->getStoreName();
$deals = $storeParser->getDeals($lineParameters);

if (empty($deals)) {
    Logger::log('No changes since the last time', 'notice');
    die;
}

$notifier = new EmailNotifier($deals, $lineParameters);
$notifier->notify('deals', $storeName, $listingName);