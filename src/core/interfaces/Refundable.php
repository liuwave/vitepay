<?php

namespace vitepay\core\interfaces;

interface Refundable
{
    public function getRefundNo();

    public function getExtra($name);

    public function getAmount();

    public function getChannel();

    /**
     * @return Payable
     */
    public function getCharge();
  
}