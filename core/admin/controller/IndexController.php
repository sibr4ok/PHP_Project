<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController{

    protected $name;

    protected function inputData(){ 

        $db = Model::instance();

        $table = "teachers";

        $collor = ['red', 'blue', 'black'];

        $res = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => 'Masha, Ivan, Igor', 'id' => 1, 'fio' => 'berezovski', 'car' => 'Porshe', 'color' => $collor],
            'operand' => ['IN', '%LIKE%', '<>', '=', 'NOT IN'],
            'condition' => ['OR', 'AND'],
            'order' => ['fio', 'name'], 
            'order_direction' => ['ASC','DESC'],
            'limit' => '1'
        ]);

        exit('I am admin panel');
    }

    protected function outputData(){

    }
}