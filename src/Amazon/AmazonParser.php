<?php
namespace braulioRam\dealsNotifier\Amazon;

use Curl\Curl;
use braulioRam\dealsNotifier\Base\Logger;
use braulioRam\dealsNotifier\Base\WebStoreParser;

Class AmazonParser extends WebStoreParser {
    protected function getNextLink($content)
    {
        $regex = '@id="pagnNextLink"[^>]+href="(?<next_link>[^"]+)">@is';
        $url = false;
        $path = '';

        if (preg_match($regex, $content, $match)) {
            $path = trim($match['next_link']);
        }

        if (!empty($path)) {
            $url = $this->domain . $path;
        }

        return $url;
    }


    protected function getProductsFromPage($content)
    {
        $regex = '@<div class="s-item-container".*?(?=<div class="s-item-container"|</ul>)@is';

        if (!preg_match_all($regex, $content, $matches)) {
            Logger::log("No matches in listing", 'warning');
        }

        foreach ($matches[0] ?: [] as $product) {
            $product = $this->processProduct($product);

            if (!empty($product['name']) && !empty($product['url']) && !empty($product['price'])) {
                $products[] = $product;
            }
        }

        return $products ?: [];
    }


    protected function processProduct($product)
    {
        return [
            'name' => $this->getProductName($product),
            'price' => $this->getProductPrice($product),
            'retail-price' => $this->getProductRetailPrice($product),
            'url' => $this->getProductUrl($product),
            'prime' => $this->getProductIsPrime($product)
        ];
    }


    protected function getProductName($product)
    {
        $regex = '@<h2[^>]*>(?<name>[^<]+)</h2>@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No name for item {$product}", 'warning');
            return '';
        }

        return trim($match['name']);
    }


    protected function getProductPrice($product)
    {
        $regex = '@<span class="[^"]*(s|a-color)-price[^"]*">\$?(?<price>[^<]+)@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No price for item {$product}", 'warning');
            return '';
        }

        return trim($match['price']);
    }


    protected function getProductRetailPrice($product)
    {
        $regex = '@<span aria-label="Suggested Retail Price:\s*\$?(?<price>[^"]+)@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No retail price for item", 'debug');
            return '';
        }

        return trim($match['price']);
    }


    protected function getProductUrl($product)
    {
        $regex = '@<a class="[^"]*s-access-detail-page[^>]*href="(?<url>[^"]+)"@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No url for item {$product}", 'warning');
            return '';
        }

        return trim($match['url']);
    }


    protected function getProductIsPrime($product)
    {
        return stripos($product, '<span class="a-icon-alt">prime</span>') === false
            ? 0
            : 1;
    }
}
