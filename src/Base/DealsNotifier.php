<?php
namespace braulioRam\dealsNotifier\Base;

use League\CLImate\CLImate;

abstract Class StoreParser {
    public function __construct()
    {
        $this->logger = new CLImate();  
        $this->readArguments();
    }


    public function notifyDeals()
    {
        $content = $this->getPageContents();
    }
}
