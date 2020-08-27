<?php

namespace vitepay\core\traits;

use Exception;
use think\Model;
use vitepay\core\interfaces\Payable;
use vitepay\Payment;

/**
 * Trait RefundableModel
 * @package vitepay\core\traits
 * @mixin Model
 */
trait RefundableModel
{
    protected function getExtraAttr($extra)
    {
        return json_decode($extra, true);
    }
    
    protected function setExtraAttr($extra)
    {
        return json_encode($extra);
    }
    
    private function getAttrOrNull($name)
    {
        try {
            return $this->getAttr($name);
        }
        catch (Exception $e) {
            return null;
        }
    }
    
    public function getRefundNo()
    {
        return $this->getAttrOrNull('refund_no');
    }
    
    public function getExtra($name)
    {
        $extra = $this->getAttrOrNull('extra');
        
        if (isset($extra[ $name ])) {
            return $extra[ $name ];
        }
    }
    
    public function getAmount()
    {
        return $this->getAttrOrNull('amount');
    }
    
    public function getGateway()
    {
        return $this->getAttrOrNull('gateway');
    }
    
    /**
     * @return Payable
     */
    public function getCharge()
    {
        return $this->getAttr('charge');
    }
    
    public function refund()
    {
        return $this->invoke(
          function (Payment $payment) {
              $payment->gateway($this->getGateway())
                ->refund($this);
          }
        );
    }
    
    public function query()
    {
        return $this->invoke(
          function (Payment $payment) {
              $payment->gateway($this->getGateway())
                ->refundQuery($this);
          }
        );
    }
}
