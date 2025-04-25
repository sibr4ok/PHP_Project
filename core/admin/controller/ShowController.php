<?php

namespace core\admin\controller;

class ShowController extends BaseAdmin
{

    protected function inputData(){

        //parent::inputData() - не будет работать с плагинами
        $this->exectBase(); 

        $this->createTableData();

        exit();

    }

    protected function outputData(){

    }

}