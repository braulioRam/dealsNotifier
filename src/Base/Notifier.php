<?php
namespace braulioRam\dealsNotifier\Base;

abstract Class Notifier {
    public function __construct(array $data, array $parameters)
    {
    	$this->data = $data;
    	$this->setParameters($parameters);
    }

    protected abstract function setParameters(array $parameters);
}
