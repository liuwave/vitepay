<?php

namespace vitepay;

use think\Request;

class NotifyController
{
    public function index(Request $request, Payment $payment, $gateway)
    {
        return $payment->gateway($gateway)->completePurchase($request);
    }
}
