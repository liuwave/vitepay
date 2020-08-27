<?php

return [
    'sandbox'    => true,//沙箱模式
    'charge'     => 'app\\model\\Charge',
    'notify_url' => '',//留空则设为PAY_NOTIFY对应的路由
    'route'      => true,//是否注册路由
];
