<?php
namespace braulioRam\dealsNotifier\Base;

use braulioRam\dealsNotifier\Base\Logger;

Class ProductsTracker {
    protected $storageFolder;
    protected $lastRecords = [];


    public function __construct($storeName, $pathName)
    {
        $basepath = STORAGE;
        $this->storageFolder = implode('/', [$basepath, $storeName, $pathName]);
    }


    public function store($data)
    {
        $lastRecords = $this->getLastRecords(false);
        $formattedData = $this->formatData($data);

        if ($lastRecords == $formattedData) {
            Logger::log('No changes', 'debug');
            return false;
        }

        $this->lastRecords = $this->unformatData($lastRecords);

        if (!is_dir($this->storageFolder)) {
            mkdir($this->storageFolder, 0777, true);
        }

        $filename = '/' . date('Y-m-d--H:i:s') . '.json';
        file_put_contents($this->storageFolder . $filename, $formattedData);

        return true;
    }


    protected function formatData($data, $asArray = false)
    {
        $orderedData = [];

        foreach ($data as $key => $value) {
            $hash = md5($value['url']);
            $orderedData[$hash] = $value; 
        }

        ksort($orderedData);

        if (!$asArray) {
            $orderedData = json_encode($orderedData, JSON_PRETTY_PRINT);
        }

        return $orderedData;
    }


    protected function unformatData($data)
    {
        return json_decode($data, true);
    }


    public function getPriceDecreases($data)
    {
        $decreases = [];

        if (!$this->store($data)) {
            return $decreases;
        }

        $data = $this->formatData($data, true);
        $priorData = $this->lastRecords;

        if (!$priorData) {
            Logger::log('No prior info', 'debug');
            return $decreases;
        }

        foreach ($data as $key => $value) {
            if (isset($priorData[$key])) {
                $oldPrice = intval(str_replace(',', '', $priorData[$key]['price']));
                $newPrice = intval(str_replace(',', '', $value['price']));

                if ($newPrice < $oldPrice) {
                    $value['discount'] = "$" . ($oldPrice - $newPrice);
                    $value['prior_price'] = $priorData[$key]['price'];
                    $decreases[] = $value;
                }
            }
        }

        return $decreases;
    }


    public function getChanges($data)
    {
        $changes = [];

        if (!$this->store($data)) {
            return $changes;
        }

        $data = $this->formatData($data, true);
        $priorData = $this->lastRecords;

        if (!$priorData) {
            Logger::log('No prior info', 'debug');
            return $changes;
        }

        foreach ($data as $key => $value) {
            if (isset($priorData[$key])) {
                $oldPrice = intval(str_replace(',', '', $priorData[$key]['price']));
                $newPrice = intval(str_replace(',', '', $value['price']));

                if ($newPrice < $oldPrice) {
                    $value['prior_price'] = $priorData[$key]['price'];
                    $value['discount'] = "$" . ($oldPrice - $newPrice);
                    $changes['decreases']['items'][] = $value;
                }

                if ($newPrice > $oldPrice) {
                    $value['prior_price'] = $priorData[$key]['price'];
                    $value['rise'] = "$" . ($newPrice - $oldPrice);
                    $changes['rises']['items'][] = $value;
                }

                continue;
            }

            $changes['new_products']['items'][] = $value;
        }

        foreach ($priorData as $key => $value) {
            if (!isset($data[$key])) {
                $changes['removed_products']['items'][] = $value;
            }
        }

        ksort($changes);

        return $changes;
    }


    protected function getLastRecords($asArray = true)
    {
        $files = glob($this->storageFolder . "/*.json");

        if (empty($files)) {
            return;
        }

        $files = array_combine($files, array_map("filemtime", $files));
        arsort($files);
        $latestFile = key($files);
        $data = file_get_contents($latestFile);

        if ($asArray) {
            $data = $this->unformatData($data);
        }

        return $data;
    }
}
