<?php

namespace core\base\controller;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseController
{
    use \core\base\controller\BaseMethods;

    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    protected $styles;
    protected $scripts;

    public function route(){
        #имя класса 
        $controller = str_replace('/', '\\', $this->controller);

        try{
            #проверил существованеи метода request в классу $controller
            $object = new \ReflectionMethod($controller, 'request');

            $args = [
                'parametrs' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];   
            #создал объект, вызвал метод request и передал массив аргументов при помощи метода ivnoke
            $object->invoke(new $controller, $args);
        }
        catch(\ReflectionException $e){
            throw new RouteException($e->getMessage());
        }
    }
    #нужно принять массив аргументов
    public function request($args){

        $this->parameters = $args['parametrs'];

        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];

        $data = $this->$inputData();

        if(method_exists($this, $outputData)){

            $page = $this->$outputData($data);
            if($page) $this->page = $page;

        }elseif($data){

            $this->page = $data;

        }
        

        if($this->errors){
            $this->writeLog($this->errors);
        }

        $this->getPage();

    }
    #шаблонизатор
    protected function render($path = '', $parameters = []){

        extract($parameters);

        if(!$path){

            $class = new \ReflectionClass($this);

            #получаем строку namespace класса (core\user\controller)
            $space = str_replace('\\', '/', $class->getNamespaceName() . '\\'); 

            $routes = Settings::get('routes');

            #сравниваем полученную строку с тем чт оу нас хранится в settings чтобы понять какой путь подключать
            if($space === $routes['user']['path']) $template = TEMPLATE;
                else $template = ADMIN_TEMPLATE;

            #с помощью new \ReflectionClass($this))->getShortName() получаем имя класса приводим его в нижний регистр и обрезаем explode, в конце вызываем нулевой элемент 
            $path = $template . explode('controller', strtolower($class->getShortName()))[0];
        }

        ob_start();

        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path);

        return ob_get_clean();
    }

    protected function getPage(){

        if(is_array($this->page)){
            foreach($this->page as $block) echo $block;
        }else{
            echo $this->page;
        }

        exit();
    }
    
    protected function init($admin = false){
        if(!$admin){
            if(USERS_CSS_JS['styles']){
                foreach(USERS_CSS_JS['styles'] as $item) $this->styles[] = PATH . TEMPLATE . trim($item, '/');
            }

            if(USERS_CSS_JS['scripts']){
                foreach(USERS_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . TEMPLATE . trim($item, '/');
            }
        }else{
            if(ADMIN_CSS_JS['styles']){
                foreach(ADMIN_CSS_JS['styles'] as $item) $this->styles[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }

            if(ADMIN_CSS_JS['scripts']){
                foreach(ADMIN_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }
        }

    }

}