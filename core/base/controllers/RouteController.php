<?php

namespace core\base\controllers;

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
}