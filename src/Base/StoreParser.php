<?php
namespace braulioRam\dealsNotifier\Base;

use braulioRam\dealsNotifier\Stores;
use League\CLImate\CLImate;

abstract Class StoreParser {
    protected $listings;
    protected $listing;
    protected $storeName;
    protected $productsTracker;


    public function __construct($settings = [])
    {
        $this->loadSettings($settings);
        $this->productsTracker = new ProductsTracker($this->storeName, $this->listing);
        $this->setListing();
    }


    protected function loadSettings(array $settings)
    {
        foreach ($settings as $setting => $value) {
            if (property_exists(get_called_class(), $setting)) {
                $this->$setting = $value;
            }
        }
    }


    protected function setListing()
    {
        if (empty($this->listings) || !is_array($this->listings)) {
            throw new Exception("Listings missing for store " . get_called_class());
        }

        $this->listing = !empty($this->listings[$this->listing])
            ? $this->listings[$this->listing]
            : end($this->listings);
    }


    public function getDeals()
    {
        $content = $this->getContents();
        $products = $this->getProducts($content);
        $deals = $this->getDiscounts($products);

        return $deals;
    }


    protected function getDiscounts(array $products)
    {
        return $this->productsTracker->getPriceDecreases($products);
    }


    protected abstract function getProducts($content);
}
