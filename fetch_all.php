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
$changes = $storeParser->getChanges($lineParameters);

if (empty($changes)) {
    Logger::log('No changes since the last time', 'notice');
    die;
}

$notifier = new EmailNotifier($changes, $lineParameters);
$notifier->notify('all');