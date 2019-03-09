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

        $userAgent = $this->getUserAgent()[array_rand($this->getUserAgent())];
        $this->curl()->setUserAgent($userAgent);
        $this->curl->setOpt(CURLOPT_ENCODING , 'gzip');
        $wait = rand(10, 15);
        Logger::log('Avoiding banhammer ' . $wait, 'notice');
        sleep($wait);
        $this->curl()->get($url);

        while ($this->curl()->error && $tries < 10) {
            $tries++;
            Logger::log("Can't fetch {$url}, retrying {$tries}", 'warning');
            Logger::log('Avoiding banhammer ' . $wait, 'notice');
            sleep($wait);
            $this->curl()->get($url);
            $wait += 2;
        }

        if ($this->curl()->error) {
            Logger::log("Can't fetch {$url}", 'error');
            throw new Exception("Exit per source error");
        }

        return $this->curl()->response;
    }


    protected function getUserAgent()
    {
        return [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0',
            // 'Mozilla/4.0 (compatible; U; MSIE 6.0; Windows NT 5.1)',
            // 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0',
            // 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
            // 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36 OPR/38.0.2220.41',
            // 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
            // 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36'
        ];
    }


    protected function getProducts($content, &$nextPagePath)
    {
        $products = $this->getProductsFromPage($content);
        $retries = 1;

        while ($products === false && $retries < 5) {
            Logger::log('Cooling down 5 min', 'notice');
            sleep(300);
            $retries++;
            $content = $this->getPageContents($nextPagePath);
            $products = $this->getProductsFromPage($content);
        }

        $nextPagePath = $this->getNextLink($content);

        if (!empty($nextPagePath)) {
            $content = $this->getPageContents($nextPagePath);
            $moreProducts = $this->getProducts($content, $nextPagePath);

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
