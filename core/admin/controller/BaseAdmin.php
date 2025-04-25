<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseAdmin extends BaseController
{

    protected $model;// через это свойство будет обращаться и вызывать методы модели

    protected $table;// какую таблицу данных подключить
    protected $columns;

    protected $menu;
    protected $title;

    protected function inputData(){

        $this->init(true);

        $this->title = 'VG engine';
        
        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projecTables');

        $this->sendNoCacheHeaders();

    }

    protected function outputData(){

    }

    protected function sendNoCacheHeaders(){

        header("Last-Modifiend: " . gmdate("D, d m Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0,pre-check=0");

    }

    protected function exectBase(){
        self::inputData();
    }

    protected function createTableData(){

        if(!$this->table){
            if($this->parameters) $this->table = array_keys($this->parameters)[0];
                else $this->table = Settings::get('defaultTable');

        }

        $this->columns = $this->model->showColumns($this->table);

        if(!$this->columns) new RouteException('Не найдены поля в таблице -' . $this->table, 2);
    }
}