<?php

namespace vitepay\facade;

use think\Facade;

class Payment extends Facade
{
    protected static function getFacadeClass()
    {
        return \vitepay\Payment::class;
    }
}
