<?php

namespace vitepay;

use InvalidArgumentException;
use think\helper\Arr;
use think\helper\Str;
use think\Manager;
use vitepay\core\Gateway;
use vitepay\core\interfaces\Payable;

/**
 * Class Payment
 * @package vitepay
 */
class Payment extends Manager
{
    /**
     * @var string
     */
    protected $namespace = '\\vitepay\\';
    
    /**
     * @param string $name
     *
     * @return \vitepay\core\Gateway
     */
    public function gateway(string $name) : Gateway
    {
        return $this->driver($name);
    }
    
    /**
     * @param string|null $name
     * @param null        $default
     *
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('vitepay.'.$name, $default);
        }
        
        return $this->app->config->get('vitepay');
    }
    
    /**
     * @param string $channel
     * @param string $gateway
     * @param null   $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getGatewayConfig(string $channel, string $gateway, $name = null, $default = null)
    {
        $channelConfig = $this->getChannelConfig($channel);
        
        $gateways = Arr::get($channelConfig, 'gateways');
        $credentials = Arr::get($channelConfig, 'credentials');
        
        if (empty($credentials)) {
            throw new InvalidArgumentException("Channel [{$channel}] credentials option not found");
        }
        //指定网关的配置
        if (!empty($gateways[ $gateway ])) {
            $config = $gateways[ $gateway ];
            
            if (!empty($config[ 'credentials' ])) {
                $config[ 'credentials' ] = array_replace(
                  $credentials,
                  array_filter((array)$config[ 'credentials' ])
                );
            }
            $channelConfig = array_merge($channelConfig, $config);
        }
        
        return Arr::get($channelConfig, $name);
    }
    
    /**
     * @param      $channel
     * @param null $name
     * @param null $default
     *
     * @return mixed
     */
    public function getChannelConfig($channel, $name = null, $default = null)
    {
        if ($config = $this->app->config->get("vitepay_${channel}")) {
            return Arr::get($config, $name, $default);
        }
        throw new InvalidArgumentException("Channel [$channel] not found.");
    }
    
    /**
     * @param string $name
     *
     * @return mixed|string
     */
    protected function resolveType(string $name)
    {
        [$channel, $gateway] = explode('_', $name, 2);
        
        $type = $this->getGatewayConfig($channel, $gateway, 'type', '');
        
        if (false === strpos($type, '\\')) {
            if ($gateway) {
                return $this->namespace.$channel.'\\gateway\\'.Str::studly($type);
            }
            else {
                return $this->namespace.$channel.'\\'.Str::studly($type ? : 'BaseGateway');
            }
        }
        
        return $type;
    }
    
    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function resolveConfig(string $name)
    {
        [$channel, $gateway] = explode('_', $name, 2);
        
        return $this->getGatewayConfig($channel, $gateway, 'credentials');
    }
    
    /**
     * @param string $name
     *
     * @return \vitepay\core\Gateway
     */
    protected function createDriver(string $name)
    {
        //每个支付网关单独设置调试模式
        [$channel, $gateway] = explode('_', $name, 2);
        
        /** @var \vitepay\core\Gateway $Gateway */
        $Gateway = parent::createDriver($name);
        $Gateway->setName($name);
        
        $notifyUrl = $this->getGatewayConfig($channel, $gateway, 'notify_url')
          ? : ($this->getConfig('notify_url')
            ? : url(
              'PAY_NOTIFY',
              ['gateway' => $name]
            )->domain(true));
        
        $Gateway->setNotifyUrl((string)$notifyUrl);
        
        if ($this->getGatewayConfig($channel, $gateway, 'sandbox')) {
            $Gateway->setSandbox();
        }
        
        if ($this->getGatewayConfig($channel, $gateway, 'log', false)) {
            $Gateway->setLog(true);
        }
        
        $Gateway->setChargeResolver(
          function ($tradeNo) {
              /** @var Payable $charge */
              $charge = $this->getConfig('charge');
              
              return $charge::retrieveByTradeNo($tradeNo);
          }
        );
        
        return $Gateway;
    }
    
    /**
     * @inheritDoc
     */
    public function getDefaultDriver()
    {
        return null;
    }
}
