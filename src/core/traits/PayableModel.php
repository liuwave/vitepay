<?php
namespace vitepay\core\traits;

use Carbon\Carbon;
use Exception;
use RuntimeException;
use think\Model;
use vitepay\core\entity\PurchaseResponse;
use vitepay\core\entity\PurchaseResult;
use vitepay\Payment;

/**
 * Trait PayableModel
 * @package vitepay\core\traits
 * @mixin Model
 */
trait PayableModel
{
    /**
     * @param $extra
     *
     * @return mixed
     */
    protected function getExtraAttr($extra)
    {
        return json_decode($extra, true);
    }
    
    /**
     * @param $extra
     *
     * @return string
     */protected function setExtraAttr($extra)
    {
        return json_encode($extra);
    }
    
    /**
     * @param $name
     *
     * @return mixed|null
     */private function getAttrOrNull($name)
    {
        try {
            return $this->getAttr($name);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * @param $tradeNo
     *
     * @return \think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */public static function retrieveByTradeNo($tradeNo)
    {
        return self::where('trade_no', $tradeNo)->find();
    }
    
    /**
     * @return mixed|null
     */
    public function getTradeNo()
    {
        return $this->getAttrOrNull('trade_no');
    }
    
    /**
     * @return mixed|null
     */
    public function getAmount()
    {
        return $this->getAttrOrNull('amount');
    }
    
    /**
     * @return mixed|null
     */
    public function getSubject()
    {
        return $this->getAttrOrNull('subject');
    }
    
    /**
     * @return mixed|null
     */
    public function getBody()
    {
        return $this->getAttrOrNull('body');
    }
    
    /**
     * @param $name
     *
     * @return mixed
     */public function getExtra($name)
    {
        $extra = $this->getAttrOrNull('extra');

        if (isset($extra[$name])) {
            return $extra[$name];
        }
    }
    
    /**
     * @param callable $format
     *
     * @return mixed
     */public function getExpire(callable $format)
    {
        $date = $this->getAttrOrNull('expire_time');
        if ($date) {
            return $format(Carbon::parse($date));
        }
    }

    /**
     * 获取支付网关标识
     * @return string
     */
    public function getGateway()
    {
        $channel = $this->getAttrOrNull('gateway');
        if (empty($channel)) {
            throw new RuntimeException('无法获取渠道标识!');
        }
        return $channel;
    }

    /**
     * 订单查询
     * @param string $gateway
     * @return PurchaseResult
     */
    public function query($gateway = null)
    {
        if (is_null($gateway)) {
            $gateway = $this->getGateway();
        }

        return $this->invoke(function (Payment $payment) use ($gateway) {
            return $payment->gateway($gateway)->query($this);
        });
    }

    /**
     * 支付
     * @param $gateway
     * @return PurchaseResponse
     */
    public function pay($gateway)
    {
        return $this->invoke(function (Payment $payment) use ($gateway) {
            return $payment->gateway($gateway)->purchase($this);
        });
    }

}
