<?php

namespace vitepay\core;

use Http\Adapter\Guzzle6\Client;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use think\Request;
use vitepay\core\entity\PurchaseResponse;
use vitepay\core\entity\PurchaseResult;
use vitepay\core\interfaces\Payable;
use vitepay\core\interfaces\Refundable;

/**
 * Class Gateway
 * @package vitepay\core
 */
abstract class Gateway
{
    
    /** @var \Closure */
    protected $chargeResolver;
    
    /** @var HttpClient */
    protected $httpClient;
    
    /** @var MessageFactory */
    protected $requestFactory;
    
    /**
     * @var
     */
    protected $notifyUrl;
    
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @var bool
     */
    protected $sandbox = false;
    
    /**
     * @var
     */
    protected $name;
    
    /**
     * Gateway constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $resolver = new OptionsResolver();
        
        $this->configureOptions($resolver);
        
        $this->options = $resolver->resolve($options);
        
        $this->requestFactory = new GuzzleMessageFactory();
        
        $this->httpClient = Client::createWithConfig($this->getHttpClientConfig());
    }
    
    /**
     * @return array
     */
    protected function getHttpClientConfig()
    {
        return [
          'connect_timeout' => 5,
          'timeout'         => 5,
        ];
    }
    
    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function getOption($name)
    {
        return $this->options[ $name ] ?? null;
    }
    
    /**
     * @param string $name
     * @param        $value
     */
    public function setOption(string $name, $value)
    {
        if (isset($this->options[ $name ])) {
            $this->options[ $name ] = $value;
        }
    }
    
    /**
     * @return mixed
     */
    public function getNotifyUrl() : string
    {
        return $this->notifyUrl;
    }
    
    /**
     * @param string $notifyUrl
     *
     * @return $this
     */
    public function setNotifyUrl(string $notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        
        return $this;
    }
    
    /**
     * @param       $class
     * @param mixed ...$args
     *
     * @return \vitepay\core\Request
     */
    public function createRequest($class, ...$args) : \vitepay\core\Request
    {
        /** @var \vitepay\core\Request $request */
        $request = new $class($this);
        
        ($request)(...$args);
        
        return $request;
    }
    
    /**
     * @param \vitepay\core\Request $request
     *
     * @return mixed
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function sendRequest(\vitepay\core\Request $request) : array
    {
        $request = $this->requestFactory->createRequest(
          $request->getMethod(),
          $request->getUri(),
          $request->getHeaders(),
          $request->getBody()
        );
        
        $response = $this->httpClient->sendRequest($request);
        
        return $this->handleResponse($request, $response);
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param mixed $name
     */
    public function setName($name) : void
    {
        $this->name = $name;
    }
    
    /**
     * @return bool
     */
    public function isSandbox() : bool
    {
        return $this->sandbox;
    }
    
    /**
     * @param bool $sandbox
     *
     * @return \vitepay\core\Gateway
     */
    public function setSandbox(bool $sandbox = true)
    {
        $this->sandbox = $sandbox;
        
        return $this;
    }
    
    /**
     * @param $resolver
     */
    public function setChargeResolver(\Closure $resolver)
    {
        $this->chargeResolver = $resolver;
    }
    
    /**
     * @param $tradeNo
     *
     * @return Payable
     */
    protected function retrieveCharge($tradeNo) : Payable
    {
        return ($this->chargeResolver)($tradeNo);
    }
    
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return mixed
     */
    abstract protected function configureOptions(OptionsResolver $resolver);
    
    /**
     * 付款
     *
     * @param Payable $charge
     *
     * @return PurchaseResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    abstract public function purchase(Payable $charge) : PurchaseResponse;
    
    /**
     * 退款
     *
     * @param \vitepay\core\interfaces\Refundable $refund
     *
     * @return array
     */
    abstract public function refund(Refundable $refund) : array;
    
    /**
     * 退款查询
     *
     * @param Refundable $refund
     *
     * @return array
     */
    abstract public function refundQuery(Refundable $refund) : array;
    
    /**
     * 查询
     *
     * @param Payable $charge
     *
     * @return PurchaseResult
     */
    abstract public function query(Payable $charge);
    
    /**
     * @param \think\Request $request
     *
     * @return mixed
     */
    abstract public function completePurchase(Request $request);
    
    /**
     * @param $data
     * @param $sign
     *
     * @return mixed
     */
    abstract public function verifySign($data, $sign);
    
    /**
     * @param array $params
     *
     * @return string
     */
    abstract public function generateSign(array $params) : string;
    
    /**
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    abstract protected function handleResponse(RequestInterface $request, ResponseInterface $response) : array;
    
}
