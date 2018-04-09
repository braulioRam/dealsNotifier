<?php
namespace braulioRam\dealsNotifier\Base;

use Curl\Curl;
use Exception;
use braulioRam\dealsNotifier\Base\Logger;
use braulioRam\dealsNotifier\Base\StoreParser;

abstract Class WebStoreParser extends StoreParser {
    protected $curl;
    protected $domain;


    protected function getContents()
    {
        return $this->getPageContents();
    }


    protected function getPageContents($path = null)
    {
        $tries = 0;
        $path = $path ?: $this->getListingPath();
        $path = str_ireplace($this->domain, '', $path);
        $url = $this->domain . $path;

        Logger::log('Parsing: ' . $url, 'notice');

        $this->curl()->get($url);

        while ($this->curl()->error && $tries < 3) {
            $tries++;
            Logger::log("Can't fetch {$url}, retrying {$tries}", 'warning');
            Logger::log('Avoiding banhammer', 'notice');
            sleep(2);
            $this->curl()->get($url);
        }

        if ($this->curl()->error) {
            Logger::log("Can't fetch {$url}", 'error');
            return;
        }

        return $this->curl()->response;
    }


    protected function getProducts($content)
    {
        $products = $this->getProductsFromPage($content);
        $nextPagePath = $this->getNextLink($content);

        if (false && !empty($nextPagePath)) {
            Logger::log('Avoiding banhammer', 'notice');
            sleep(2);
            $content = $this->getPageContents($nextPagePath);
            $moreProducts = $this->getProducts($content);

            if ($moreProducts) {
                $products = array_merge($products, $moreProducts);
            }
        }

        return $products;
    }


    protected abstract function getProductsFromPage($content);
    protected abstract function getNextLink($content);


    protected function getListingPath()
    {
        if (empty($this->listing)) {
            throw new Exception("Listing not set for store " . get_called_class());
        }

        return $this->listing;
    }


    protected function curl()
    {
        if (!is_object($this->curl)) {
            $this->curl = new Curl;
        }

        return $this->curl;
    }
}
