<?php

namespace core\admin\controller;

class ShowController extends BaseAdmin
{

    protected function inputData(){

        //parent::inputData() - не будет работать с плагинами
        $this->execBase(); 

        $this->createTableData();// определяет из какой таблицы тащить данные и выбирает колонки из таблицы

        $this->createData(['fields' => ['content']]);// получает необходимые данные из текущей таблицы

    }

    protected function outputData(){

    }

}