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
            'order' => [1, 'name'], 
            'order_direction' => ['ASC','DESC'],
            'limit' => '1',
            'join' => [
                'join_table1' => [
                    'table' => 'join_table1',
                    'fields' => ['id as j_id', 'name as j_name'],
                    'type' => 'left',
                    'where' => ['name' => 'sasha'],
                    'operand' => ['='],
                    'condition' => ['OR'],
                    'on' => [
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]
                ],
                'join_table2' => [
                    'table' => 'join_table2',
                    'fields' => ['id as j2_id', 'name as j2_name'],
                    'type' => 'left',
                    'where' => ['name' => 'sasha'],
                    'operand' => ['='],
                    'condition' => ['AND'],
                    'on' => [
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]
                ],
            ]
        ]);

        exit('I am admin panel');
    }

    protected function outputData(){

    }
}