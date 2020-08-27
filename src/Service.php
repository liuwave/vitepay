<?php

namespace vitepay;

use think\Route;

class Service extends \think\Service
{
    public function boot()
    {
        if ($group = $this->app->config->get('vitepay.route')) {
            $this->registerRoutes(function (Route $route) use ($group) {
                if (is_string($group)) {
                    $rule = "{$group}/pay/:gateway/notify";
                } else {
                    $rule = "pay/:gateway/notify";
                }
                $route->any($rule, '\\vitepay\\NotifyController@index')
                    ->completeMatch()
                    ->name('PAY_NOTIFY');
            });
        }
    }
}
