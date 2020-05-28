<?php
namespace braulioRam\dealsNotifier\Amazon;

use braulioRam\dealsNotifier\Base\Logger;
use braulioRam\dealsNotifier\Base\WebStoreParser;

Class AmazonParser extends WebStoreParser {
    protected function getNextLink($content)
    {
        $regex = '@(?:id="pagnNextLink"[^>]+href="|<li class="a-last"><a href=")(?<next_link>[^"]+)@is';
        $url = false;
        $path = '';

        if (preg_match($regex, $content, $match)) {
            $path = trim($match['next_link']);
        }

        if (!empty($path)) {
            $path = str_replace('&amp;', '&', $path);
            $url = $this->domain . $path;
        }

        return $url;
    }


    protected function getProductsFromPage($content)
    {
        $content = preg_replace('@id="centerBelowExtraSponsoredLinks".*@is', '', $content);
        $regex = '@<div (?:class="s-item-container"|data-asin).*?(?=<div (?:class="s-item-container"|data-asin|class="s-result-list-placeholder))@is';

        if (!preg_match_all($regex, $content, $matches)) {
            Logger::log("No matches in listing, retrying", 'warning');
            return false;
        }

        Logger::log("Items: " . count($matches[0]), 'debug');

        foreach ((array) $matches[0] as $key => $product) {
            if (stripos($product, 'pagnPrevString') !== false || stripos($product, '<script type="text/javascript">') !== false) {
                Logger::log("Skipping index: {$key}", 'warning');
                continue;
            }

            $product = $this->processProduct($product);

            if (!empty($product['name']) && !empty($product['url']) && !empty($product['price'])) {
                $products[] = $product;
            }
        }

        return $products ?: [];
    }


    protected function processProduct($product)
    {
        $name = $this->getProductName($product);

        return [
            'name' => $name,
            'price' => $this->getProductPrice($product, $name),
            'retail_price' => $this->getProductRetailPrice($product),
            'url' => $this->getProductUrl($product),
            'prime' => $this->getProductIsPrime($product)        
        ];
    }


    protected function getProductName($product)
    {
        $regex = '@class="s-image"\s+alt="(?<name>[^"]+)@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No name for item {$product}", 'warning');
            return '';
        }

        return html_entity_decode(trim($match['name']));
    }


    protected function getProductPrice($product, $name = '')
    {
        $regex = '@(?:<span class="[^"]*(s|a-color)-price[^"]*">|<span class="a-price" data-a-size="l" data-a-color="base"><span class="a-offscreen">|M(?:á|&aacute;)s opciones de compra</span><br><span class="a-color-base">)\$?(?<price>[^<]+)@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No price for item {$name}", 'warning');
            print_r($product);
            return '';
        }

        $price = trim($match['price']);

        if (stripos($price, '-') !== false) {
            $price = explode('-', $price);
            $price = trim(reset($price));
        }

        return $price;
    }


    protected function getProductRetailPrice($product)
    {
        $regex = '@(<span aria-label="Suggested Retail Price:\s*|<span class="a-price" data-a-size="b" data-a-strike="true" data-a-color="secondary"><span class="a-offscreen">)\$?(?<price>[^"<]+)@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No retail price for item", 'debug');
            return '';
        }

        return trim($match['price']);
    }


    protected function getProductUrl($product)
    {
        $regex = '@<a class="[^"]*(?:s-access-detail-page|a-link-normal)[^>]*href="(?<url>[^"]+)@is';

        if (!preg_match($regex, $product, $match)) {
            Logger::log("No url for item {$product}", 'warning');
            return '';
        }

        $domain = 'https://www.amazon.com.mx';
        return $domain . str_ireplace('https://www.amazon.com.mx', '', trim(preg_replace('@(.+/dp/[^/]+).*@is', '$1', $match['url'])));
    }


    protected function getProductIsPrime($product)
    {
        $regex = '@<span class="a-icon-alt">prime</span>|<i class="a-icon a-icon-prime a-icon-medium" role="img" aria-label="Amazon Prime"></i>@is';

        return preg_match($regex, $product)
            ? 1
            : 0;
    }
}
