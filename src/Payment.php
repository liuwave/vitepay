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
    public function getGatewayConfig(string $channel, ?string $gateway = '', $name = null, $default = null)
    {
        /*全部配置*/
        $config = $this->getChannelConfig($channel);
        
        if (empty($config[ 'credentials' ])) {
            throw new InvalidArgumentException("Channel [{$channel}] credentials option not found");
        }
        //指定网关的配置
        if ($gateway) {
            $gatewayConfig = Arr::get($config, "gateways.{$gateway}");
            
            if ($gatewayConfig) {
                $credentials = (isset($gatewayConfig[ 'credentials' ]) && is_array($gatewayConfig[ 'credentials' ]))
                  ? array_filter($gatewayConfig[ 'credentials' ]) : [];
                
                $gatewayConfig[ 'credentials' ] = array_replace($config[ 'credentials' ], $credentials);
                
                $config = array_merge($config, $gatewayConfig);
            }
        }
        
        Arr::forget($config, 'gateways');
        
        return Arr::get($config, $name);
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
        
        return $this->getGatewayConfig($channel, $gateway,  'credentials');
    }
    
    /**
     * @param string $name
     *
     * @return \vitepay\core\Gateway
     */
    protected function createDriver(string $name)
    {
        /** @var \vitepay\core\Gateway $gateway */
        $gateway = parent::createDriver($name);
        $gateway->setName($name);
        
        $notifyUrl = $this->getConfig('notify_url') ? : url('PAY_NOTIFY', ['gateway' => $name])->domain(true);
        $gateway->setNotifyUrl((string)$notifyUrl);
    
        if ($this->getConfig('sandbox')) {
            $gateway->setSandbox();
        }
        
        $gateway->setChargeResolver(
          function ($tradeNo) {
              /** @var Payable $charge */
              $charge = $this->getConfig('charge');
              
              return $charge::retrieveByTradeNo($tradeNo);
          }
        );
        
        return $gateway;
    }
    
    /**
     * @inheritDoc
     */
    public function getDefaultDriver()
    {
        return null;
    }
}
