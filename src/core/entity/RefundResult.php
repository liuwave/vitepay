<?php


namespace vitepay\core\entity;

class RefundResult
{
    
    protected $raw;
    
    protected $gateway;
    
    public function __construct($gateway)
    {
        $this->gateway = $gateway;
    }
    
    /**
     * @return mixed
     */
    public function getGateway()
    {
        return $this->gateway;
    }
    
    /**
     * @param null $name
     *
     * @return mixed
     */
    public function getRaw($name = null)
    {
        if (is_null($name)) {
            return $this->raw;
        }
        elseif (isset($this->raw[ $name ])) {
            return $this->raw[ $name ];
        }
    }
}