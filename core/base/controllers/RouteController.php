<?php

namespace core\base\controllers;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    //шаблон пректирования singleton
    static private $_instance;

    static public function getInstance(){
        if(self::$_instance instanceof self){
            return self::$_instance;
        }

        return self::$_instance = new self;
    }
    private function __construct()
    {
        $s = Settings::instance('routes');
        $s1 = ShopSettings::instance('routes');
        exit();
    }
}