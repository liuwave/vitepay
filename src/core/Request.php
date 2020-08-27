<?php

namespace vitepay\core;

abstract class Request
{
    /** @var \vitepay\core\Gateway */
    protected $gateway;
    
    protected $params = [];
    
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }
    
    abstract public function getMethod();
    
    abstract public function getUri();
    
    abstract public function getHeaders();
    
    abstract public function getBody();
}
