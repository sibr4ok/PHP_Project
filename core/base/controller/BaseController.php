<?php

namespace core\base\controller;

use core\base\exceptions\RouteException;

abstract class BaseController
{

    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

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

        $this->$inputData();

        $this->page = $this->$outputData();

        if($this->errors){
            #$this->writeLog();
        }

        $this->getPage();

    }
    #шаблонизатор
    protected function render($path = '', $parameters = []){

        extract($parameters);

        if(!$path){
            #с помощью new \ReflectionClass($this))->getShortName() получаем имя класса приводим его в нижний регистр и обрезаем explode, в конце вызываем нулевой элемент 
            $path = TEMPLATE . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0];
        }

        ob_start();

        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path);

        return ob_get_clean();
    }

    protected function getPage(){
        exit($this->page);
    }

}