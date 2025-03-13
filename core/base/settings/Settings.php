<?php

namespace core\base\settings;

class Settings{
    static private $_instance;

    private $routes = [
        'admin' => [
            'name' => 'admin',
            'path' => 'core/admin/controller/',
            'hrUrl' => false
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins'=> [
            'path' => 'core/plugins/',
            'hrUrl'=> false
        ],
        'user'=>[
            'paht' => 'core/user/controller/',
            'hrUrl' => true,
            'routes' => []
        ],
        'default' => [
            'controller' => 'IndexController',
            'inputMethod' => 'inputData',
            'outputMethod' => 'outputData'
        ]
    ];

    private $teplateArr = [
        'text' => ['name', 'phone', 'adress'],
        'textarea' => ['content', 'keywords']
    ];
    
    private $lalala = 'lalal';

    private function __construct()
    {

    }
    
    private function __clone()
    {
        
    }

    static public function get($property){
        return self::instance()->$property;
    }

    static public function instance()
    {
        if(self::$_instance instanceof self){
            return self::$_instance;
        }
        return self::$_instance = new self;
    }

    public function clueProperties($class){
        $baseProperties = [];

        foreach ($this as $name => $item){
            $property = $class::get($name);
            if(is_array($property) && is_array($item)){
                $baseProperties[$name] = $this->arrayMergeRecursive($this->$name, $property);
                continue;
            }

            if(!$property) $baseProperties[$name] = $this->$name;

        }

        return $baseProperties;
    }
    # Метод склейки массивов по порядку(если ключи int) и если текстовые ключи совпадает то перезаписывает 
    public function arrayMergeRecursive(){
        $arrays = func_get_args();//из памяти принимает аргументы

        $base = array_shift($arrays);// возвращает первый элемент массива удаляя его из поданного на вход массива 
        
        foreach($arrays as $array){
            foreach($array as $key => $value){
                if(is_array($value) && is_array($base[$key])){
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                }else{
                    if(is_int($key)){//проверяет целое число
                        if(!in_array($value, $base)){  //проверяет существуе значение в массиве
                            array_push($base, $value);//закидывает в массив base значение value
                            continue;
                        }
                    }
                    $base[$key] = $value;
                }
            }
        }

        return $base;

    }

}