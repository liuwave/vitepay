<?php
/**
 * Created by PhpStorm.
 * User: liuwave
 * Date: 2020/8/26 17:37
 * Description:
 */

namespace vitepay\core;

class Helper
{
    public static function getGatewayClassName($shortName)
    {
        if (0 === strpos($shortName, '\\')) {
            return $shortName;
        }
        
        // replace underscores with namespace marker, PSR-0 style
        $shortName = str_replace('_', '\\', $shortName);
        if (false === strpos($shortName, '\\')) {
            $shortName .= '\\';
        }
        
        return '\\vitepay\\'.$shortName.'Gateway';
    }
}