<?php
namespace vitepay\core\interfaces;

use vitepay\core\entity\PurchaseResult;

/**
 * Interface Payable
 * @package vitepay\core\interfaces
 */
interface Payable
{
    /**
     * @return string
     */
    public function getTradeNo() ;
    
    /**
     * @return integer
     */
    public function getAmount();
    
    /**
     * @return string
     */
    public function getSubject();
    
    /**
     * @return string
     */
    public function getBody();
    
    /**
     * @param $name
     *
     * @return mixed
     */
    public function getExtra($name);
    
    /**
     * @param callable $format
     *
     * @return mixed
     */
    public function getExpire(callable $format);
    
    /**
     * @return bool
     */
    public function isComplete();
    
    /**
     * @param \vitepay\core\entity\PurchaseResult $result
     *
     * @return mixed
     */
    public function onComplete(PurchaseResult $result);
    
    /**
     * @param $orderNo
     *
     * @return self
     */
    public static function retrieveByTradeNo($orderNo);
    
}