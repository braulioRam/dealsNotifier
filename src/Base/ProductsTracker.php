<?php
namespace braulioRam\dealsNotifier\Base;

Class ProductsTracker {
    protected $storageFolder;


    public function __construct($storeName, $pathName)
    {
        $basepath = dirname(dirname(__DIR__)) . '/data';
        $this->storageFolder = implode('/', [$basepath, $storeName, $pathName]);
    }


    public function store($data)
    {
        $formattedData = $this->formatData($data);
        $lastRecords = $this->getLastRecords(false);

        if ($lastRecords == $formattedData) {
            return false;
        }

        if (!is_dir($this->storageFolder)) {
            mkdir($this->storageFolder, 0777, true);
        }

        $filename = '/' . date('Y-m-d--H:i') . '.json';
        file_put_contents($this->storageFolder . $filename, $formattedData);

        return true;
    }


    protected function formatData($data)
    {
        $orderedData = [];

        foreach ($data as $key => $value) {
            $hash = md5($value['url']);
            $orderedData[$hash] = $value; 
        }

        return json_encode($orderedData, JSON_PRETTY_PRINT);
    }


    protected function unformatData($data)
    {
        return json_decode($data, true);
    }


    public function compare($field)
    {
        
    }


    public function getPriceDecreases($data)
    {
        $decreases = [];

        if (!$this->store($data)) {
            return $decreases;
        }

        $priorData = $this->getLastRecords();

        foreach ($data as $key => $value) {
            if (isset($priorData[$key])) {
                if ($priorData[$key]['price'] < $data[$key]['price']) {
                    $decreases[] = $value;
                }
            }
        }

        return $decreases;
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
