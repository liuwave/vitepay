<?php

namespace vitepay\core\entity;

use Carbon\Carbon;

class PurchaseResult
{
    protected $raw;
    
    protected $gateway;
    
    protected $tradeNo;
    
    protected $amount;
    
    protected $isPaid;
    
    protected $payTime;
    
    public function __construct($gateway, $tradeNo, $amount, $isPaid, $payTime, $raw)
    {
        $this->gateway = $gateway;
        $this->tradeNo = $tradeNo;
        $this->amount  = $amount;
        $this->isPaid  = $isPaid;
        $this->payTime = $payTime;
        $this->raw     = $raw;
    }
    
    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }
    
    /**
     * @return bool
     */
    public function isPaid()
    {
        return $this->isPaid;
    }
    
    /**
     * @return Carbon
     */
    public function getPayTime()
    {
        return $this->payTime;
    }
    
    /**
     * @return string
     */
    public function getTradeNo()
    {
        return $this->tradeNo;
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
    
    /**
     * @return string
     */
    public function getGateway()
    {
        return $this->gateway;
    }
}
