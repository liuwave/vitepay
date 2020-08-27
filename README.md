# vitepay
一个thinkphp6的多网关支付框架。



## 安装

    composer require vitepay/core vitepay/wechat
    
## 使用

修改 `config/vitepay.php`

	return [
		'sandbox'    => true,//沙箱模式
		'charge'     => 'app\\model\\Charge',
		'notify_url' => '',//留空则设为PAY_NOTIFY对应的路由
		'route'      => true,//是否注册路由
	];

需要指定 `charge`模型，并实现[Payable](https://github.com/liuwave/vitepay/blob/master/src/core/interfaces/Payable.php)

    /**@var \vitepay\core\traits\PayableModel $charge */
    $charge=\app\model\Charge::find()
    
    //起调支付 
    $charge->pay('wechat_js');
    
    //查询结果
    
    $charge->query();    
    
    //判断结果
    
    $charge->isComplete();
    
    
    
    

## 支持的支付网关

所有的支付网关需要实现[Gateway](https://github.com/liuwave/vitepay/blob/master/src/core/Gateway.php).

当前可用支付网关如下：

网关 | Composer包 | 简介 | 作者
--- | --- | ------ | --- |
wechat|vitepay/wechat|微信支付|liuwave|
alipay|vitepay/alipay|支付宝支付|liuwave|




## 感谢


- [thephpleague/omnipay](https://github.com/thephpleague/omnipay)
- [think-pay](https://github.com/yunwuxin/think-pay)


## License

The MIT License (MIT). Please see [License File](https://choosealicense.com/licenses/mit) for more information.

