<?php
namespace braulioRam\dealsNotifier\Base;

use braulioRam\dealsNotifier\Stores;
use Exception;

Class StoreIdentifier {
    protected $stores = [];
    protected $parameters = [];
    protected $genericStore = '';


    public function __construct($parameters = [])
    {
        $this->stores = Stores::getStores();

        if (empty($this->stores)) {
            throw new Exception("No stores found");
        }

        $this->genericStore = end($this->stores);
        $storeNames = array_keys($this->stores);
        $this->genericStoreName = end($storeNames);
        $this->parameters = $parameters;
    }


    public function getStoreParser()
    {
        $store = $this->genericStore;
        $storeName = $this->genericStoreName;

        if (isset($this->stores[$this->parameters['store']])) {
            $store = $this->stores[$this->parameters['store']];
            $storeName = $this->parameters['store'];
        }

        $listings = array_keys($store['listings']);
        $listing = isset($this->parameters['listing'])
            ? $this->parameters['listing']
            : end($listings);

        $extraParameters['listing'] = $listing;
        $extraParameters['storeName'] = $storeName;

        return isset($this->stores[$this->parameters['store']])
            ? new $this->stores[$this->parameters['store']['parser']](array_merge($this->parameters['store'], $extraParameters))
            : new $this->genericStore['parser'](array_merge($this->genericStore, $extraParameters));
    }
}
